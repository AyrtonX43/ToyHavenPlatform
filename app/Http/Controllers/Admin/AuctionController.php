<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Notifications\AuctionListingApprovedNotification;
use App\Notifications\AuctionListingRejectedNotification;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with('user', 'category', 'images');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $auctions = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.auctions.index', compact('auctions'));
    }

    public function show(Auction $auction)
    {
        $auction->load('user', 'category', 'images', 'bids.user');

        return view('admin.auctions.show', compact('auction'));
    }

    public function approve(Auction $auction)
    {
        if ($auction->status !== Auction::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Only pending auctions can be approved.');
        }

        $auction->update(['status' => Auction::STATUS_ACTIVE]);
        $auction->user->notify(new AuctionListingApprovedNotification($auction));

        return back()->with('success', 'Auction approved and is now live.');
    }

    public function reject(Request $request, Auction $auction)
    {
        if ($auction->status !== Auction::STATUS_PENDING_APPROVAL) {
            return back()->with('error', 'Only pending auctions can be rejected.');
        }

        $request->validate(['feedback' => ['required', 'string', 'max:1000']]);

        $auction->update(['status' => Auction::STATUS_CANCELLED]);
        $auction->user->notify(new AuctionListingRejectedNotification($auction, $request->feedback));

        return back()->with('success', 'Auction rejected. Seller has been notified.');
    }
}
