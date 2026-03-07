<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['auctionSellerProfile.user', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $auctions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.index', compact('auctions'));
    }

    public function show(Auction $auction)
    {
        $auction->load(['auctionSellerProfile.user', 'category', 'images', 'bids.user']);

        return view('admin.auctions.show', compact('auction'));
    }

    public function approve(Auction $auction)
    {
        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Only pending auctions can be approved.');
        }

        $auction->update([
            'status' => 'approved',
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Auction approved. It will go live at the scheduled start time.');
    }

    public function reject(Request $request, Auction $auction)
    {
        if ($auction->status !== 'pending_approval') {
            return back()->with('error', 'Only pending auctions can be rejected.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:500']);

        $auction->update([
            'status' => 'draft',
            'rejection_reason' => $request->rejection_reason,
        ]);

        return back()->with('success', 'Auction rejected. Seller can edit and resubmit.');
    }

    public function cancel(Auction $auction)
    {
        if ($auction->status === 'ended') {
            return back()->with('error', 'Cannot cancel an ended auction.');
        }

        $auction->update(['status' => 'cancelled']);

        return back()->with('success', 'Auction cancelled.');
    }
}
