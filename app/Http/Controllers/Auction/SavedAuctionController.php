<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SavedAuctionController extends Controller
{
    public function save(Auction $auction)
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return back()->with('error', 'Membership required to save auctions.');
        }

        if ($auction->user_id === $user->id) {
            return back()->with('error', 'You cannot save your own listing.');
        }

        $exists = \Illuminate\Support\Facades\DB::table('saved_auctions')
            ->where('user_id', $user->id)
            ->where('auction_id', $auction->id)
            ->exists();

        if (! $exists) {
            \Illuminate\Support\Facades\DB::table('saved_auctions')->insert([
                'user_id' => $user->id,
                'auction_id' => $auction->id,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Auction saved to your watchlist.');
    }

    public function unsave(Auction $auction)
    {
        $user = Auth::user();

        \Illuminate\Support\Facades\DB::table('saved_auctions')
            ->where('user_id', $user->id)
            ->where('auction_id', $auction->id)
            ->delete();

        return back()->with('success', 'Auction removed from your watchlist.');
    }
}
