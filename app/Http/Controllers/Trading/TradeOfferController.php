<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\TradeListing;
use App\Models\TradeOffer;
use App\Notifications\TradeOfferAcceptedNotification;
use App\Notifications\TradeOfferReceivedNotification;
use App\Notifications\TradeOfferRejectedNotification;
use App\Services\TradeOfferService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TradeOfferController extends Controller
{
    public function __construct(
        protected TradeOfferService $offerService
    ) {
        $this->middleware('auth');
        $this->middleware('trade.not.suspended');
    }

    /**
     * Show form to make an offer on a listing (select listing to offer for exchange, or enter cash for cash).
     */
    public function offerForm($id)
    {
        $listing = TradeListing::with(['user', 'images'])->findOrFail($id);
        if ($listing->user_id === Auth::id()) {
            return redirect()->route('trading.listings.show', $id)->with('error', 'You cannot make an offer on your own listing.');
        }
        if (!$listing->canAcceptOffers()) {
            return redirect()->route('trading.listings.show', $id)->with('error', 'This listing is not accepting offers.');
        }

        // Reference/sync from My Listings: only show exchange/exchange_with_cash when target is exchange-type
        $myListings = collect();
        if (in_array($listing->trade_type, ['exchange', 'exchange_with_cash'])) {
            $myListings = TradeListing::where('user_id', Auth::id())
                ->where('status', 'active')
                ->where('id', '!=', $id)
                ->whereIn('trade_type', ['exchange', 'exchange_with_cash'])
                ->with(['images', 'userProduct', 'product', 'category'])
                ->orderByDesc('created_at')
                ->get();
        }

        return view('trading.offers.create', compact('listing', 'myListings'));
    }

    public function store(Request $request, $id)
    {
        $listing = TradeListing::with('user')->findOrFail($id);

        if ($listing->user_id === Auth::id()) {
            return back()->with('error', 'You cannot make an offer on your own listing.');
        }
        if (!$listing->canAcceptOffers()) {
            return back()->with('error', 'This listing is not accepting offers.');
        }

        $rules = [
            'message' => 'nullable|string|max:1000',
            'cash_amount' => 'nullable|numeric|min:0',
            'offer_listing_id' => 'nullable|exists:trade_listings,id',
        ];
        if (in_array($listing->trade_type, ['exchange', 'exchange_with_cash'])) {
            $rules['offered_user_product_id'] = 'nullable|exists:user_products,id';
            $rules['offered_product_id'] = 'nullable|exists:products,id';
            $rules['offer_listing_id'] = 'required|exists:trade_listings,id';
        }
        if ($listing->trade_type === 'cash') {
            $rules['cash_amount'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $offeredUserProductId = $validated['offered_user_product_id'] ?? null;
        $offeredProductId = $validated['offered_product_id'] ?? null;
        $offeredTradeListingId = null;
        if (!empty($validated['offer_listing_id'])) {
            $offerListing = TradeListing::find($validated['offer_listing_id']);
            if ($offerListing && $offerListing->user_id === Auth::id()) {
                $offeredUserProductId = $offerListing->user_product_id;
                $offeredProductId = $offerListing->product_id;
                // Standalone listing (no product/user_product) – use offered_trade_listing_id
                if (!$offeredUserProductId && !$offeredProductId) {
                    $offeredTradeListingId = $offerListing->id;
                }
            }
        }

        $data = [
            'offerer_id' => Auth::id(),
            'offerer_seller_id' => Auth::user()->seller?->id,
            'offered_product_id' => $offeredProductId,
            'offered_user_product_id' => $offeredUserProductId,
            'offered_trade_listing_id' => $offeredTradeListingId,
            'cash_amount' => $validated['cash_amount'] ?? null,
            'message' => $validated['message'] ?? null,
        ];

        try {
            $offer = $this->offerService->create((int) $id, $data);
            $listing->user->notify(new TradeOfferReceivedNotification($offer));
            return redirect()->route('trading.offers.my')
                ->with('success', 'Offer sent. The seller will be notified.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function myOffers()
    {
        $offers = TradeOffer::where('offerer_id', Auth::id())
            ->with(['tradeListing.images', 'tradeListing.user', 'offeredProduct.images', 'offeredUserProduct.images', 'offeredTradeListing.images'])
            ->orderByDesc('created_at')
            ->paginate(15);

        $suggestedListings = TradeListing::with(['images', 'user'])
            ->where('status', 'active')
            ->where('user_id', '!=', Auth::id())
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->inRandomOrder()
            ->limit(8)
            ->get();

        return view('trading.offers.my', compact('offers', 'suggestedListings'));
    }

    public function offersReceived()
    {
        $listings = TradeListing::where('user_id', Auth::id())
            ->with(['activeOffers.offerer', 'activeOffers.offeredProduct.images', 'activeOffers.offeredUserProduct.images', 'images'])
            ->whereHas('activeOffers')
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('trading.offers.received', compact('listings'));
    }

    public function show($id)
    {
        $offer = TradeOffer::with(['tradeListing.images', 'tradeListing.user', 'offerer', 'offeredProduct.images', 'offeredUserProduct.images'])
            ->findOrFail($id);

        if ($offer->offerer_id !== Auth::id() && $offer->tradeListing->user_id !== Auth::id()) {
            abort(403);
        }

        return view('trading.offers.show', compact('offer'));
    }

    public function accept(Request $request, $id)
    {
        $offer = TradeOffer::with('tradeListing')->findOrFail($id);
        if ($offer->tradeListing->user_id !== Auth::id()) {
            abort(403);
        }

        try {
            $trade = $this->offerService->acceptOffer((int) $id);
            $offer->offerer->notify(new TradeOfferAcceptedNotification($offer, $trade));
            return redirect()->route('trading.trades.show', $trade->id)
                ->with('success', 'Offer accepted. Proceed to schedule your meetup.');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $offer = TradeOffer::with('tradeListing')->findOrFail($id);
        if ($offer->tradeListing->user_id !== Auth::id()) {
            abort(403);
        }

        $offer->update(['status' => 'rejected']);
        $offer->offerer->notify(new TradeOfferRejectedNotification($offer));
        return back()->with('success', 'Offer rejected.');
    }
}
