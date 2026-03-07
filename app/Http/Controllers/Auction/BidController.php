<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BidController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return back()->with('error', 'Active membership required to bid.');
        }

        if ($user->isAuctionSuspended()) {
            return back()->with('error', 'You are suspended from auction bidding.');
        }

        if ($auction->status !== 'live') {
            return back()->with('error', 'This auction is not accepting bids.');
        }

        $planIds = $auction->allowed_bidder_plan_ids;
        if ($planIds !== null && $planIds !== []) {
            $plan = $user->currentPlan();
            if (! $plan || ! in_array($plan->id, $planIds)) {
                return back()->with('error', 'Your membership plan cannot bid on this auction.');
            }
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:' . ($auction->starting_bid + ($auction->bid_increment ?? 1)),
        ]);

        $currentHigh = $auction->bids()->orderByDesc('amount')->first();
        $minBid = $currentHigh
            ? $currentHigh->amount + ($auction->bid_increment ?? 1)
            : $auction->starting_bid;

        if ($validated['amount'] < $minBid) {
            return back()->with('error', 'Your bid must be at least ₱' . number_format($minBid, 0) . '.');
        }

        $existingBid = $auction->bids()->where('user_id', $user->id)->orderByDesc('amount')->first();
        $anonymousId = $existingBid?->anonymous_display_id ?? AuctionBid::generateAnonymousDisplayId();

        AuctionBid::where('auction_id', $auction->id)->update(['is_winning' => false]);

        AuctionBid::create([
            'auction_id' => $auction->id,
            'user_id' => $user->id,
            'amount' => $validated['amount'],
            'anonymous_display_id' => $anonymousId,
            'is_winning' => true,
        ]);

        $auction->increment('bids_count');

        return back()->with('success', 'Bid placed successfully!');
    }
}
