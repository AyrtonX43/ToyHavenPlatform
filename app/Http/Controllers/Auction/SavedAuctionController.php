<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\SavedAuction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedAuctionController extends Controller
{
    public function index()
    {
        $saved = SavedAuction::where('user_id', Auth::id())
            ->with('auction.images')
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('auctions.saved', compact('saved'));
    }

    public function store(Auction $auction)
    {
        $user = Auth::user();

        SavedAuction::firstOrCreate(
            ['user_id' => $user->id, 'auction_id' => $auction->id]
        );

        return back()->with('success', 'Auction saved. We\'ll notify you when it goes live.');
    }

    public function destroy(Auction $auction)
    {
        SavedAuction::where('user_id', Auth::id())
            ->where('auction_id', $auction->id)
            ->delete();

        return back()->with('success', 'Removed from saved.');
    }
}
