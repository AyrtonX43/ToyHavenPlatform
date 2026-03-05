<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorAction;
use App\Models\TradeListing;
use App\Notifications\TradeListingApprovedNotification;
use App\Notifications\TradeListingRejectedNotification;
use Illuminate\Http\Request;

class TradeListingController extends Controller
{
    public function index(Request $request)
    {
        $query = TradeListing::with(['user', 'images', 'category']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhereHas('user', fn ($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        $listings = $query->orderByDesc('created_at')->paginate(20);
        return view('moderator.trade-listings.index', compact('listings'));
    }

    public function show($id)
    {
        $listing = TradeListing::with([
            'user',
            'images',
            'category',
            'activeOffers.offerer',
            'activeOffers.offeredProduct',
            'activeOffers.offeredUserProduct',
        ])->findOrFail($id);

        return view('moderator.trade-listings.show', compact('listing'));
    }

    public function approveListing($id)
    {
        $listing = TradeListing::with('user')->findOrFail($id);

        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'Only listings pending review can be approved.');
        }

        $listing->update(['status' => 'active']);
        $listing->user->notify(new TradeListingApprovedNotification($listing));

        ModeratorAction::log(auth()->id(), 'trade_listing_approved', $listing, 'Trade listing approved', [
            'listing_id' => $listing->id,
            'listing_title' => $listing->title,
        ]);

        return back()->with('success', 'Listing approved. User has been notified.');
    }

    public function rejectListing(Request $request, $id)
    {
        $listing = TradeListing::with('user')->findOrFail($id);

        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'Only listings pending review can be rejected.');
        }

        $reason = $request->input('rejection_reason', '');
        $listing->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        $listing->user->notify(new TradeListingRejectedNotification($listing, $reason));

        ModeratorAction::log(auth()->id(), 'trade_listing_rejected', $listing, 'Trade listing rejected', [
            'listing_id' => $listing->id,
            'reason' => $reason,
        ]);

        return back()->with('success', 'Listing rejected. User has been notified.');
    }
}
