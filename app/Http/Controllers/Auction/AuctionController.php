<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Support\Facades\Schema;

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

        $activeListings = Auction::with(['user', 'category', 'images'])
            ->active()
            ->orderByDesc('end_at')
            ->paginate(12);

        $pendingListings = Auction::with(['user', 'category', 'images'])
            ->pendingApproval()
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        return view('auction.index', compact('activeListings', 'pendingListings'));
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

        if (! $auction->isActive() && ! $auction->isEnded() && ! $auction->isPendingApproval()) {
            return redirect()->route('auction.index')
                ->with('error', 'This auction is not available for viewing.');
        }

        $with = ['user', 'category', 'images'];
        if (Schema::hasTable('auction_bids')) {
            $with['bids'] = fn ($q) => $q->orderByDesc('amount')->limit(10);
        }
        $auction->load($with);
        if (! $auction->relationLoaded('bids')) {
            $auction->setRelation('bids', collect([]));
        }

        $isSaved = Schema::hasTable('saved_auctions') && \Illuminate\Support\Facades\DB::table('saved_auctions')
            ->where('user_id', $user->id)
            ->where('auction_id', $auction->id)
            ->exists();

        return view('auction.show', compact('auction', 'isSaved'));
    }
}
