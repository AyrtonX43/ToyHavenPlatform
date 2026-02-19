<?php

namespace App\Http\Controllers\Auction;

use App\Events\AuctionBidPlaced;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BidController extends Controller
{
    public function store(Request $request, Auction $auction)
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership() && ! $user->isAdmin()) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('error', 'You need an active membership to place bids.');
        }

        $request->validate([
            'amount' => ['required', 'numeric', 'min:0'],
        ]);

        $minBid = $auction->getMinNextBid();
        if ((float) $request->amount < $minBid) {
            return back()->with('error', "Minimum bid is ₱".number_format($minBid, 2));
        }

        if (! $auction->canUserBid($user)) {
            return back()->with('error', 'You cannot bid on this auction.');
        }

        try {
            DB::transaction(function () use ($auction, $user, $request) {
                AuctionBid::where('auction_id', $auction->id)->update(['is_winning' => false]);

                $bid = AuctionBid::create([
                    'auction_id' => $auction->id,
                    'user_id' => $user->id,
                    'amount' => $request->amount,
                    'is_winning' => true,
                ]);

                $auction->update(['bids_count' => $auction->bids()->count()]);

                event(new AuctionBidPlaced($auction, $bid));
            });
        } catch (\Exception $e) {
            return back()->with('error', 'Could not place bid. Please try again.');
        }

        return back()->with('success', 'Your bid has been placed.');
    }
}
