<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Http\Request;

class AuctionHistoryController extends Controller
{
    public function index(Request $request)
    {
        $payments = AuctionPayment::where('winner_id', $request->user()->id)
            ->with('auction.images')
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('auctions.history', compact('payments'));
    }
}
