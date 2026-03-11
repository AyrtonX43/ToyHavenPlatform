<?php

namespace App\Http\Controllers\Auction;

use App\Events\AuctionExtended;
use App\Events\BidPlaced;
use App\Events\UserOutbid;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Notifications\AuctionOutbidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AuctionBidController extends Controller
{
    private const ANTI_SNIPE_THRESHOLD_SECONDS = 120;
    private const ANTI_SNIPE_EXTENSION_MINUTES = 2;
    private const BID_COOLDOWN_SECONDS = 3;

    public function store(Request $request, Auction $auction)
    {
        $user = Auth::user();
        $isAjax = $request->expectsJson();

        if (! $user->hasActiveMembership()) {
            return $isAjax
                ? response()->json(['error' => 'Membership required to place bids.'], 403)
                : redirect()->route('auction.index')->with('error', 'Membership required to place bids.');
        }

        if (! $auction->isActive()) {
            return $isAjax
                ? response()->json(['error' => 'This auction is not accepting bids.'], 422)
                : back()->with('error', 'This auction is not accepting bids.');
        }

        if ($auction->user_id === $user->id) {
            return $isAjax
                ? response()->json(['error' => 'You cannot bid on your own listing.'], 403)
                : back()->with('error', 'You cannot bid on your own listing.');
        }

        if ($user->auction_suspended_until && $user->auction_suspended_until->isFuture()) {
            return $isAjax
                ? response()->json(['error' => 'Your auction access is suspended.'], 403)
                : back()->with('error', 'Your auction access is suspended.');
        }

        if ($user->auction_banned_at) {
            return $isAjax
                ? response()->json(['error' => 'You are banned from auction bidding.'], 403)
                : back()->with('error', 'You are banned from auction bidding.');
        }

        $cooldownKey = "auction_bid_cooldown:{$auction->id}:{$user->id}";
        if (Cache::has($cooldownKey)) {
            return $isAjax
                ? response()->json(['error' => 'Please wait a few seconds before bidding again.'], 429)
                : back()->with('error', 'Please wait a few seconds before bidding again.');
        }

        $currentBid = $auction->winning_amount !== null ? (float) $auction->winning_amount : (float) $auction->starting_bid;
        $minBid = round($currentBid + (float) $auction->bid_increment, 2);
        $submittedAmount = round((float) $request->input('amount', 0), 2);

        if ($submittedAmount < $minBid) {
            $msg = 'Your bid must be at least ₱' . number_format($minBid, 2) . '.';
            return $isAjax
                ? response()->json(['error' => $msg], 422)
                : back()->with('error', $msg);
        }

        if ($submittedAmount > $minBid) {
            $msg = 'Please place only the minimum next bid (₱' . number_format($minBid, 2) . ').';
            return $isAjax
                ? response()->json(['error' => $msg], 422)
                : back()->with('error', $msg);
        }

        $amount = $minBid;
        $bidderAlias = '';
        $antiSnipeTriggered = false;

        DB::transaction(function () use ($auction, $user, $amount, &$bidderAlias, &$antiSnipeTriggered) {
            $previousWinningBid = AuctionBid::where('auction_id', $auction->id)
                ->where('is_winning', true)
                ->first();

            if ($previousWinningBid && $previousWinningBid->user_id !== $user->id) {
                try {
                    $previousWinningBid->user->notify(new AuctionOutbidNotification($auction, $amount));
                } catch (\Exception $e) {
                    Log::error('Failed to send outbid notification: ' . $e->getMessage());
                }

                try {
                    broadcast(new UserOutbid($previousWinningBid->user_id, $auction, $amount));
                } catch (\Exception $e) {
                    Log::error('Failed to broadcast outbid event: ' . $e->getMessage());
                }
            }

            AuctionBid::where('auction_id', $auction->id)->update(['is_winning' => false]);

            $rank = AuctionBid::where('auction_id', $auction->id)->max('rank_at_bid') ?? 0;
            $rank++;
            $bidderAlias = 'Bidder #' . $rank;

            AuctionBid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'rank_at_bid' => $rank,
                'is_winning' => true,
            ]);

            $auction->update([
                'winner_id' => $user->id,
                'winning_amount' => $amount,
                'bids_count' => $auction->bids_count + 1,
            ]);

            $auction->refresh();

            if ($auction->end_at) {
                $secondsLeft = now()->diffInSeconds($auction->end_at, false);
                if ($secondsLeft >= 0 && $secondsLeft <= self::ANTI_SNIPE_THRESHOLD_SECONDS) {
                    $newEndAt = $auction->end_at->copy()->addMinutes(self::ANTI_SNIPE_EXTENSION_MINUTES);
                    $auction->update(['end_at' => $newEndAt]);
                    $auction->refresh();
                    $antiSnipeTriggered = true;
                }
            }
        });

        Cache::put($cooldownKey, true, self::BID_COOLDOWN_SECONDS);

        $auction->refresh();

        $endAtIso = $auction->end_at?->toIso8601String() ?? '';

        try {
            broadcast(new BidPlaced(
                auction: $auction,
                amount: $amount,
                bidderAlias: $bidderAlias,
                bidCount: $auction->bids_count,
                nextMinBid: $auction->next_min_bid,
                endAt: $endAtIso,
            ))->toOthers();
        } catch (\Exception $e) {
            Log::error('Failed to broadcast BidPlaced: ' . $e->getMessage());
        }

        if ($antiSnipeTriggered) {
            try {
                broadcast(new AuctionExtended($auction));
            } catch (\Exception $e) {
                Log::error('Failed to broadcast AuctionExtended: ' . $e->getMessage());
            }
        }

        if ($isAjax) {
            return response()->json([
                'success' => true,
                'message' => 'Your bid of ₱' . number_format($amount, 2) . ' has been placed!',
                'bid' => [
                    'amount' => $amount,
                    'amount_formatted' => '₱' . number_format($amount, 2),
                    'bidder_alias' => $bidderAlias,
                    'bid_count' => $auction->bids_count,
                    'next_min_bid' => $auction->next_min_bid,
                    'next_min_bid_formatted' => '₱' . number_format($auction->next_min_bid, 2),
                    'end_at' => $endAtIso,
                    'anti_snipe' => $antiSnipeTriggered,
                ],
            ]);
        }

        return back()->with('success', 'Your bid of ₱' . number_format($amount, 2) . ' has been placed successfully.');
    }
}
