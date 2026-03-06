<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'membership']);
    }

    public function store(Request $request, Auction $auction)
    {
        $user = Auth::user();

        if ($user->isAuctionBanned()) {
            return back()->with('error', 'You are banned from bidding on auctions.');
        }

        if ($user->isAuctionSuspended()) {
            return back()->with('error', 'Your bidding privileges are currently suspended.');
        }

        if (! $auction->canBid()) {
            return back()->with('error', 'This auction is not accepting bids.');
        }

        $minBid = $auction->nextMinBid();

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:' . $minBid],
        ], [
            'amount.min' => "Minimum bid is ₱" . number_format($minBid, 2),
        ]);

        $amount = (float) $validated['amount'];

        if ($amount < $minBid) {
            return back()->with('error', "Minimum bid is ₱" . number_format($minBid, 2));
        }

        DB::transaction(function () use ($auction, $user, $amount) {
            $nextRank = ($auction->bids()->max('rank_at_bid') ?? 0) + 1;
            $auction->bids()->where('is_winning', true)->update(['is_winning' => false]);

            AuctionBid::create([
                'auction_id' => $auction->id,
                'user_id' => $user->id,
                'amount' => $amount,
                'rank_at_bid' => $nextRank,
                'is_winning' => true,
            ]);

            $auction->increment('bids_count');
        });

        return back()->with('success', 'Bid placed successfully!');
    }
}
