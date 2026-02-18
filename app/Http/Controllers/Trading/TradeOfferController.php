<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\TradeListing;
use App\Models\TradeOffer;
use App\Models\Product;
use App\Models\UserProduct;
use App\Services\TradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradeOfferController extends Controller
{
    protected $tradeService;

    public function __construct(TradeService $tradeService)
    {
        $this->middleware('auth');
        $this->tradeService = $tradeService;
    }

    public function myOffers()
    {
        $offers = TradeOffer::where('offerer_id', Auth::id())
            ->with([
                'tradeListing.product.images',
                'tradeListing.userProduct.images',
                'tradeListing.user',
                'offeredProduct.images',
                'offeredUserProduct.images',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('trading.offers.my-offers', compact('offers'));
    }

    public function offersOnMyListings()
    {
        $listings = TradeListing::where('user_id', Auth::id())
            ->with([
                'offers.offeredProduct.images',
                'offers.offeredUserProduct.images',
                'offers.offerer',
                'offers.offererSeller',
            ])
            ->has('offers')
            ->get();

        return view('trading.offers.received', compact('listings'));
    }

    public function store(Request $request, $listingId)
    {
        $listing = TradeListing::findOrFail($listingId);

        if ($listing->user_id === Auth::id()) {
            return back()->with('error', 'You cannot make an offer on your own listing.');
        }

        if (!$listing->canAcceptOffers()) {
            return back()->with('error', 'This listing is not accepting offers.');
        }

        $validated = $request->validate([
            'product_type' => 'required|in:user_product,seller_product',
            'product_id' => 'nullable|integer',
            'user_product_id' => 'nullable|integer',
            'cash_amount' => 'nullable|numeric|min:0',
            'message' => 'nullable|string|max:1000',
        ]);

        // Validate that at least one product is provided
        if ($validated['product_type'] === 'seller_product' && !$validated['product_id']) {
            return back()->withInput()->with('error', 'Please select a product to offer.');
        }
        if ($validated['product_type'] === 'user_product' && !$validated['user_product_id']) {
            return back()->withInput()->with('error', 'Please select a product to offer.');
        }

        DB::beginTransaction();
        try {
            $offererSellerId = null;
            if (Auth::user()->isSeller() && Auth::user()->seller) {
                $offererSellerId = Auth::user()->seller->id;
            }

            $offeredProductId = null;
            $offeredUserProductId = null;

            if ($validated['product_type'] === 'seller_product') {
                $product = Product::where('id', $validated['product_id'])
                    ->where(function($q) use ($offererSellerId) {
                        if ($offererSellerId) {
                            $q->where('seller_id', $offererSellerId);
                        } else {
                            $q->where('user_id', Auth::id());
                        }
                    })
                    ->where('is_tradeable', true)
                    ->where('trade_status', 'available_for_trade')
                    ->firstOrFail();
                $offeredProductId = $product->id;
            } else {
                $userProduct = UserProduct::where('user_id', Auth::id())
                    ->where('id', $validated['user_product_id'])
                    ->where('status', 'available')
                    ->firstOrFail();
                $offeredUserProductId = $userProduct->id;
            }

            $offer = TradeOffer::create([
                'trade_listing_id' => $listing->id,
                'offerer_id' => Auth::id(),
                'offerer_seller_id' => $offererSellerId,
                'offered_product_id' => $offeredProductId,
                'offered_user_product_id' => $offeredUserProductId,
                'cash_amount' => $validated['cash_amount'] ?? null,
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
            ]);

            $listing->increment('offers_count');

            DB::commit();

            // TODO: Send notification to listing owner

            return redirect()->route('trading.listings.show', $listing->id)
                ->with('success', 'Offer submitted successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to create offer: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        $offer = TradeOffer::with([
            'tradeListing.product.images',
            'tradeListing.userProduct.images',
            'tradeListing.user',
            'offeredProduct.images',
            'offeredUserProduct.images',
            'offerer',
            'offererSeller',
        ])->findOrFail($id);

        $canManage = $offer->tradeListing->user_id === Auth::id() || $offer->offerer_id === Auth::id();

        return view('trading.offers.show', compact('offer', 'canManage'));
    }

    public function accept($id)
    {
        $offer = TradeOffer::with('tradeListing')->findOrFail($id);

        if ($offer->tradeListing->user_id !== Auth::id()) {
            return back()->with('error', 'You can only accept offers on your own listings.');
        }

        if (!$offer->canBeAccepted()) {
            return back()->with('error', 'This offer cannot be accepted.');
        }

        try {
            $trade = $this->tradeService->acceptOffer($offer->id);
            return redirect()->route('trading.trades.show', $trade->id)
                ->with('success', 'Offer accepted! Trade created successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to accept offer: ' . $e->getMessage());
        }
    }

    public function reject($id)
    {
        $offer = TradeOffer::with('tradeListing')->findOrFail($id);

        if ($offer->tradeListing->user_id !== Auth::id()) {
            return back()->with('error', 'You can only reject offers on your own listings.');
        }

        if ($offer->status !== 'pending') {
            return back()->with('error', 'This offer cannot be rejected.');
        }

        $offer->update(['status' => 'rejected']);

        // TODO: Send notification to offerer

        return back()->with('success', 'Offer rejected.');
    }

    public function counterOffer(Request $request, $id)
    {
        $originalOffer = TradeOffer::with('tradeListing')->findOrFail($id);

        if ($originalOffer->tradeListing->user_id !== Auth::id()) {
            return back()->with('error', 'You can only counter offer on your own listings.');
        }

        if ($originalOffer->status !== 'pending') {
            return back()->with('error', 'This offer cannot be countered.');
        }

        $validated = $request->validate([
            'cash_amount' => 'nullable|numeric|min:0',
            'message' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $counterOffer = TradeOffer::create([
                'trade_listing_id' => $originalOffer->trade_listing_id,
                'offerer_id' => Auth::id(),
                'offerer_seller_id' => $originalOffer->tradeListing->seller_id,
                'offered_product_id' => $originalOffer->tradeListing->product_id,
                'offered_user_product_id' => $originalOffer->tradeListing->user_product_id,
                'cash_amount' => $validated['cash_amount'] ?? null,
                'message' => $validated['message'] ?? null,
                'status' => 'pending',
                'counter_offer_id' => $originalOffer->id,
            ]);

            $originalOffer->update(['status' => 'counter_offered']);

            DB::commit();

            // TODO: Send notification

            return back()->with('success', 'Counter offer created successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to create counter offer: ' . $e->getMessage());
        }
    }

    public function withdraw($id)
    {
        $offer = TradeOffer::findOrFail($id);

        if ($offer->offerer_id !== Auth::id()) {
            return back()->with('error', 'You can only withdraw your own offers.');
        }

        if (!$offer->canBeWithdrawn()) {
            return back()->with('error', 'This offer cannot be withdrawn.');
        }

        $offer->update(['status' => 'withdrawn']);

        // TODO: Send notification

        return back()->with('success', 'Offer withdrawn successfully.');
    }
}
