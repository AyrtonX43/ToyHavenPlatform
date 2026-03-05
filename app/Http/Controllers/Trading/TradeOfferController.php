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
        ];
        if (in_array($listing->trade_type, ['exchange', 'exchange_with_cash'])) {
            $rules['offered_user_product_id'] = 'nullable|exists:user_products,id';
            $rules['offered_product_id'] = 'nullable|exists:products,id';
            if ($listing->trade_type === 'exchange') {
                $rules['offered_user_product_id'] = 'required_without:offered_product_id';
                $rules['offered_product_id'] = 'required_without:offered_user_product_id';
            }
        }
        if ($listing->trade_type === 'cash') {
            $rules['cash_amount'] = 'required|numeric|min:0';
        }

        $validated = $request->validate($rules);

        $data = [
            'offerer_id' => Auth::id(),
            'offerer_seller_id' => Auth::user()->seller?->id,
            'offered_product_id' => $validated['offered_product_id'] ?? null,
            'offered_user_product_id' => $validated['offered_user_product_id'] ?? null,
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
            ->with(['tradeListing.images', 'tradeListing.user', 'offeredProduct.images', 'offeredUserProduct.images'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('trading.offers.my', compact('offers'));
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
