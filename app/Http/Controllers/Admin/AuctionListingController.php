<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionListingController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::with(['user', 'category'])->orderByDesc('updated_at');

        $status = $request->query('status');
        if ($status && in_array($status, ['draft', 'pending_approval', 'active', 'ended', 'cancelled'])) {
            $query->where('status', $status);
        }

        $listings = $query->paginate(20);

        return view('admin.auction-listings.index', compact('listings'));
    }

    public function show(Auction $listing)
    {
        $listing->load(['user', 'category']);

        return view('admin.auction-listings.show', compact('listing'));
    }

    public function approve(Auction $listing)
    {
        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'This listing is not pending approval.');
        }

        $durationHours = $listing->duration_hours ?? 24;
        $startAt = now();
        $endAt = now()->addHours($durationHours);

        $listing->update([
            'status' => 'active',
            'start_at' => $startAt,
            'end_at' => $endAt,
            'rejection_reason' => null,
        ]);

        return back()->with('success', 'Auction listing approved and is now live.');
    }

    public function reject(Request $request, Auction $listing)
    {
        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'This listing is not pending approval.');
        }

        $request->validate([
            'feedback' => 'required|string|max:2000',
        ]);

        $listing->update([
            'status' => 'draft',
            'rejection_reason' => $request->feedback,
        ]);

        return back()->with('success', 'Auction listing rejected. The seller has been notified and can resubmit with corrections.');
    }
}
