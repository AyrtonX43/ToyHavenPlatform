<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (! auth()->user()->hasAuctionPermission('auctions_view')) {
                abort(403, 'You do not have permission to view auctions.');
            }

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $query = Auction::with(['user', 'category', 'winner']);

        if ($request->filled('status')) {
            if ($request->status === 'live') {
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

        $auctions = $query->orderByDesc('created_at')->paginate(20);

        $canModerate = auth()->user()->hasAuctionPermission('auctions_moderate');

        return view('moderator.auctions.index', compact('auctions', 'canModerate'));
    }

    public function show(Auction $auction)
    {
        $auction->load(['user', 'seller', 'product', 'userProduct', 'category', 'categories', 'images', 'bids.user', 'winner']);

        $canModerate = auth()->user()->hasAuctionPermission('auctions_moderate');

        return view('moderator.auctions.show', compact('auction', 'canModerate'));
    }

    public function approve(Auction $auction)
    {
        if (! auth()->user()->hasAuctionPermission('auctions_moderate')) {
            abort(403, 'You do not have permission to approve auctions.');
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

    public function reject(Request $request, Auction $auction)
    {
        if (! auth()->user()->hasAuctionPermission('auctions_moderate')) {
            abort(403, 'You do not have permission to reject auctions.');
        }

        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Auction is not pending approval.');
        }

        $auction->update(['status' => 'cancelled']);

        return back()->with('success', 'Auction rejected.');
    }
}
