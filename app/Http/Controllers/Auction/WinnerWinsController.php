<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Support\Facades\Auth;

class WinnerWinsController extends Controller
{
    public function index()
    {
        $wins = AuctionPayment::with(['auction.images', 'auction.category'])
            ->where('winner_id', Auth::id())
            ->where('payment_status', 'paid')
            ->orderByDesc('paid_at')
            ->paginate(10);

        return view('auctions.wins.index', compact('wins'));
    }

    public function show(AuctionPayment $auctionPayment)
    {
        if ($auctionPayment->winner_id !== Auth::id()) {
            abort(403);
        }

        if ($auctionPayment->payment_status !== 'paid') {
            return redirect()->route('auctions.wins.index')->with('error', 'Payment not found.');
        }

        $auctionPayment->load(['auction.images', 'auction.category', 'trackingUpdates', 'reviews']);

        return view('auctions.wins.show', compact('auctionPayment'));
    }
}
