<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\TradeDispute;
use App\Models\TradeListing;
use App\Notifications\TradeListingApprovedNotification;
use App\Notifications\TradeListingRejectedNotification;
use App\Services\TradeDisputeService;
use Illuminate\Http\Request;

class TradeController extends Controller
{
    public function __construct(
        protected TradeDisputeService $disputeService
    ) {
        $this->middleware('role:admin');
    }

    public function index(Request $request)
    {
        $query = Trade::with(['tradeListing.user', 'initiator', 'participant']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('tradeListing', fn ($q) => $q->where('title', 'like', "%{$search}%"));
        }

        $trades = $query->orderByDesc('created_at')->paginate(20);
        return view('admin.trades.index', compact('trades'));
    }

    public function show($id)
    {
        $trade = Trade::with([
            'tradeListing.images',
            'tradeListing.user',
            'tradeOffer',
            'initiator',
            'participant',
            'items',
            'dispute.reporter',
        ])->findOrFail($id);

        return view('admin.trades.show', compact('trade'));
    }

    public function listings(Request $request)
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
        return view('admin.trades.listings', compact('listings'));
    }

    public function showListing($id)
    {
        $listing = TradeListing::with([
            'user',
            'images',
            'category',
            'activeOffers.offerer',
            'activeOffers.offeredProduct',
            'activeOffers.offeredUserProduct',
        ])->findOrFail($id);

        return view('admin.trades.listing-show', compact('listing'));
    }

    public function approveListing($id)
    {
        $listing = TradeListing::with('user')->findOrFail($id);

        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'Only listings pending review can be approved.');
        }

        $listing->update(['status' => 'active']);
        $listing->user->notify(new TradeListingApprovedNotification($listing));
        return back()->with('success', 'Listing approved. User has been notified via email and in-app notification.');
    }

    public function rejectListing(Request $request, $id)
    {
        $listing = TradeListing::with('user')->findOrFail($id);

        if ($listing->status !== 'pending_approval') {
            return back()->with('error', 'Only listings pending review can be rejected.');
        }

        $request->validate(['rejection_reason' => 'required|string|max:1000']);

        $reason = $request->input('rejection_reason');
        $listing->update(['status' => 'rejected', 'rejection_reason' => $reason]);
        $listing->user->notify(new TradeListingRejectedNotification($listing, $reason));
        return back()->with('success', 'Listing rejected. User has been notified via email and in-app notification.');
    }

    public function resolveDispute(Request $request, $id)
    {
        $trade = Trade::findOrFail($id);
        if ($trade->status !== 'disputed') {
            return back()->with('error', 'This trade is not in dispute.');
        }

        $validated = $request->validate([
            'resolution' => 'required|in:completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        $dispute = $trade->dispute;
        if (!$dispute) {
            return back()->with('error', 'No dispute found.');
        }

        $this->disputeService->resolve($dispute, $validated['resolution'], $validated['notes'] ?? null);
        return back()->with('success', 'Dispute resolved successfully.');
    }

    public function cancelTrade($id)
    {
        $trade = Trade::findOrFail($id);
        if (in_array($trade->status, ['completed', 'cancelled'])) {
            return back()->with('error', 'Cannot cancel this trade.');
        }

        $meetupService = app(\App\Services\TradeMeetupService::class);
        $meetupService->cancel($trade);
        return back()->with('success', 'Trade cancelled.');
    }
}
