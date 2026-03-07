<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_view')) {
            abort(403, 'You do not have permission to view auctions.');
        }

        $query = Auction::with(['auctionSellerProfile.user', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $auctions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.index', compact('auctions'))->with('context', 'moderator');
    }

    public function show(Auction $auction)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_view')) {
            abort(403);
        }

        $auction->load(['auctionSellerProfile.user', 'category', 'images', 'bids']);

        return view('admin.auctions.show', compact('auction'))->with('context', 'moderator');
    }

    public function approve(Auction $auction)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_moderate')) {
            abort(403);
        }

        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Only pending auctions can be approved.');
        }

        $auction->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Auction approved.');
    }

    public function reject(Request $request, Auction $auction)
    {
        if (! Auth::user()->hasAuctionPermission('auctions_moderate')) {
            abort(403);
        }

        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Only pending auctions can be rejected.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $auction->update([
            'status' => 'draft',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Auction rejected.');
    }
}
