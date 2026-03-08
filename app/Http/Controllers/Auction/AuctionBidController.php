<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Notifications\AuctionOutbidNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AuctionBidController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')
                ->with('error', 'Membership required to place bids.');
        }

        if (! $auction->isActive()) {
            return back()->with('error', 'This auction is not accepting bids.');
        }

        if ($auction->user_id === $user->id) {
            return back()->with('error', 'You cannot bid on your own listing.');
        }

        if ($user->auction_suspended_until && $user->auction_suspended_until->isFuture()) {
            return back()->with('error', 'Your auction access is suspended.');
        }

        if ($user->auction_banned_at) {
            return back()->with('error', 'You are banned from auction bidding.');
        }

        $minBid = (float) ($auction->winning_amount ?? $auction->starting_bid) + (float) $auction->bid_increment;
        $maxBid = 99999999.99;
        $request->validate([
            'amount' => 'required|numeric|min:' . $minBid . '|max:' . $maxBid,
        ], [
            'amount.min' => 'Your bid must be at least ₱' . number_format($minBid, 2) . '.',
            'amount.max' => 'Bid cannot exceed ₱99,999,999.99.',
        ]);

        $amount = min(99999999.99, max((float) $request->amount, $minBid));

        DB::transaction(function () use ($auction, $user, $amount) {
            $previousWinningBid = AuctionBid::where('auction_id', $auction->id)->where('is_winning', true)->first();
            if ($previousWinningBid && $previousWinningBid->user_id !== $user->id) {
                try {
                    $previousWinningBid->user->notify(new AuctionOutbidNotification($auction, $amount));
                } catch (\Exception $e) {
                    \Log::error('Failed to send outbid notification: ' . $e->getMessage());
                }
            }

            AuctionBid::where('auction_id', $auction->id)->update(['is_winning' => false]);

            $rank = AuctionBid::where('auction_id', $auction->id)->max('rank_at_bid') ?? 0;
            $rank++;

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
        });

        return back()->with('success', 'Your bid of ₱' . number_format($amount, 2) . ' has been placed successfully.');
    }
}
