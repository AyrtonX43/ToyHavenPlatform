<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionController extends Controller
{
    /**
     * List auctions. Requires auctions_view permission.
     */
    public function index(Request $request)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_view')) {
            abort(403, 'You do not have permission to view auctions.');
        }

        $query = Auction::with(['user', 'category', 'winner']);

        if ($request->filled('status')) {
            if ($request->status === 'pending_approval') {
                $query->where('status', 'pending_approval');
            } elseif ($request->status === 'live') {
                $query->live();
            } elseif ($request->status === 'ended') {
                $query->ended();
            } else {
                $query->where('status', $request->status);
            }
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($sql) => $sql->where('title', 'like', "%{$q}%"));
        }

        $auctions = $query->orderByDesc('created_at')->paginate(20)->withQueryString();

        return view('moderator.auctions.index', compact('auctions'));
    }

    /**
     * Show a single auction. Requires auctions_view permission.
     */
    public function show(Auction $auction)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_view')) {
            abort(403, 'You do not have permission to view auctions.');
        }

        $auction->load(['user', 'seller', 'product', 'userProduct', 'category', 'categories', 'images', 'bids.user', 'winner']);

        return view('moderator.auctions.show', compact('auction'));
    }

    /**
     * Approve a pending auction. Requires auctions_moderate permission.
     */
    public function approve(Auction $auction)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_moderate')) {
            abort(403, 'You do not have permission to moderate auctions.');
        }

        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Auction is not pending approval.');
        }

        $auction->update([
            'status' => 'live',
            'start_at' => $auction->start_at ?? now(),
        ]);

        return back()->with('success', 'Auction approved and is now live.');
    }

    /**
     * Reject a pending auction. Requires auctions_moderate permission.
     */
    public function reject(Request $request, Auction $auction)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_moderate')) {
            abort(403, 'You do not have permission to moderate auctions.');
        }

        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Auction is not pending approval.');
        }

        $auction->update(['status' => 'cancelled']);

        return back()->with('success', 'Auction rejected.');
    }
}
