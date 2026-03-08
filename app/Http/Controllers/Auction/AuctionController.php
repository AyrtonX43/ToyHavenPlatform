<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;

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

        $activeListings = Auction::with(['user', 'category'])
            ->active()
            ->orderByDesc('end_at')
            ->paginate(12);

        return view('auction.index', compact('activeListings'));
    }

    /**
     * Show a single auction listing (public detail + bid form).
     */
    public function show(Auction $auction)
    {
        $user = auth()->user();

        if (! $user || ! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership.');
        }

        if (! $auction->isActive() && ! $auction->isEnded()) {
            return redirect()->route('auction.index')
                ->with('error', 'This auction is not available for viewing.');
        }

        $auction->load(['user', 'category', 'bids' => fn ($q) => $q->orderByDesc('amount')->limit(10)]);

        return view('auction.show', compact('auction'));
    }
}
