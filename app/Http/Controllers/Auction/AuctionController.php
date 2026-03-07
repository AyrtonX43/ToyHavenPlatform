<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;

class AuctionController extends Controller
{
    /**
     * Auction hub for members: active listings, shortcuts, etc.
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership. Please subscribe to a plan to continue.');
        }

        return view('auction.index');
    }
}
