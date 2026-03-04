<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\TradeDispute;
use App\Models\TradeReview;
use App\Models\Seller;
use App\Models\Product;
use App\Models\UserProduct;
use App\Services\TradeService;
use App\Models\TradeListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class TradeController extends Controller
{
    protected $tradeService;

    public function __construct(TradeService $tradeService)
    {
        $this->middleware('auth');
        $this->tradeService = $tradeService;
    }

    public function index()
    {
        $trades = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->with([
                'tradeListing.product.images',
                'tradeListing.userProduct.images',
                'tradeOffer',
                'initiator',
                'participant',
                'items',
            ])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('trading.trades.index', compact('trades'));
    }

    public function show($id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->with([
                'tradeListing.product.images',
                'tradeListing.userProduct.images',
                'tradeOffer.offeredProduct.images',
                'tradeOffer.offeredUserProduct.images',
                'initiator',
                'participant',
                'items',
                'conversation.messages.sender',
            ])
            ->findOrFail($id);

        $isInitiator = $trade->isInitiator(Auth::id());
        $isParticipant = $trade->isParticipant(Auth::id());
        $otherParty = $trade->getOtherParty(Auth::id());

        $userReview = TradeReview::where('trade_id', $trade->id)->where('reviewer_id', Auth::id())->first();
        $canReview = $trade->status === 'completed' && !$userReview;

        return view('trading.trades.show', compact('trade', 'isInitiator', 'isParticipant', 'otherParty', 'userReview', 'canReview'));
    }

    public function updateShipping(Request $request, $id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        $validated = $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'province' => 'required|string',
            'postal_code' => 'required|string',
            'phone' => 'required|string',
        ]);

        $addressData = [
            'address' => $validated['address'],
            'city' => $validated['city'],
            'province' => $validated['province'],
            'postal_code' => $validated['postal_code'],
            'phone' => $validated['phone'],
        ];

        if ($trade->isInitiator(Auth::id())) {
            $trade->update(['initiator_shipping_address' => $addressData]);
        } else {
            $trade->update(['participant_shipping_address' => $addressData]);
        }

        return back()->with('success', 'Shipping address updated successfully!');
    }

    public function lock($id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if (!in_array($trade->status, ['pending_shipping', 'shipped', 'received'])) {
            return back()->with('error', 'Cannot lock this trade.');
        }

        if ($trade->isInitiator(Auth::id())) {
            if ($trade->initiator_locked_at) {
                return back()->with('info', 'You have already locked the deal.');
            }
            $trade->update(['initiator_locked_at' => now()]);
        } else {
            if ($trade->participant_locked_at) {
                return back()->with('info', 'You have already locked the deal.');
            }
            $trade->update(['participant_locked_at' => now()]);
        }

        return back()->with('success', 'Deal locked! You can now proceed with shipping.');
    }

    public function markShipped(Request $request, $id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if ($trade->status !== 'pending_shipping' && $trade->status !== 'shipped') {
            return back()->with('error', 'Invalid trade status for shipping.');
        }

        if (!$trade->bothLocked()) {
            return back()->with('error', 'Both parties must lock the deal before shipping.');
        }

        $validated = $request->validate([
            'tracking_number' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            if ($trade->isInitiator(Auth::id())) {
                if (!$trade->initiator_shipping_address) {
                    return back()->with('error', 'Please set your shipping address first.');
                }
                $trade->update([
                    'initiator_tracking_number' => $validated['tracking_number'],
                    'initiator_shipped_at' => now(),
                ]);
            } else {
                if (!$trade->participant_shipping_address) {
                    return back()->with('error', 'Please set your shipping address first.');
                }
                $trade->update([
                    'participant_tracking_number' => $validated['tracking_number'],
                    'participant_shipped_at' => now(),
                ]);
            }

            // Update trade status if both parties have shipped
            if ($trade->initiator_shipped_at && $trade->participant_shipped_at) {
                $trade->update(['status' => 'shipped']);
            } else {
                $trade->update(['status' => 'shipped']);
            }

            DB::commit();

            // TODO: Send notification

            return back()->with('success', 'Item marked as shipped!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to update shipping: ' . $e->getMessage());
        }
    }

    public function markReceived(Request $request, $id)
    {
        $trade = Trade::with('tradeListing')->where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if ($trade->status !== 'shipped' && $trade->status !== 'received') {
            return back()->with('error', 'Invalid trade status for receiving.');
        }

        $request->validate([
            'proof_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $path = $request->file('proof_image')->store('trade-proofs', 'public');

            if ($trade->isInitiator(Auth::id())) {
                $trade->update([
                    'initiator_received_at' => now(),
                    'initiator_received_proof_path' => $path,
                ]);
            } else {
                $trade->update([
                    'participant_received_at' => now(),
                    'participant_received_proof_path' => $path,
                ]);
            }

            $trade->refresh();

            if ($trade->initiator_received_at && $trade->participant_received_at) {
                $trade->update(['status' => 'received']);

                $listing = $trade->tradeListing;
                if ($listing && $listing->status === 'pending_trade') {
                    $listing->update(['status' => 'completed']);
                }
            }

            DB::commit();

            return back()->with('success', 'Item marked as received with proof!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to mark as received: ' . $e->getMessage());
        }
    }

    public function complete($id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if (!$trade->canBeCompleted()) {
            return back()->with('error', 'Both parties must confirm receipt before completing the trade.');
        }

        try {
            $this->tradeService->updateTradeStatus($trade->id, 'completed');
            return back()->with('success', 'Trade completed successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to complete trade: ' . $e->getMessage());
        }
    }

    public function storeTradeReview(Request $request, $id)
    {
        $trade = Trade::with(['initiator', 'participant'])->where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if ($trade->status !== 'completed') {
            return back()->with('error', 'You can only rate completed trades.');
        }

        $revieweeId = $trade->getOtherParty(Auth::id())->id;
        if (TradeReview::where('trade_id', $trade->id)->where('reviewer_id', Auth::id())->exists()) {
            return back()->with('error', 'You have already rated this trade.');
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        TradeReview::create([
            'trade_id' => $trade->id,
            'reviewer_id' => Auth::id(),
            'reviewee_id' => $revieweeId,
            'rating' => $request->rating,
            'comment' => $request->comment,
        ]);

        $reviewee = $trade->getOtherParty(Auth::id());
        $seller = Seller::where('user_id', $reviewee->id)->first();
        if ($seller) {
            $tradeReviews = TradeReview::where('reviewee_id', $reviewee->id)->get();
            $sellerReviews = $seller->reviews()->get();
            $allRatings = $sellerReviews->pluck('overall_rating')->concat($tradeReviews->pluck('rating'))->filter();
            if ($allRatings->isNotEmpty()) {
                $seller->update([
                    'rating' => round($allRatings->avg(), 2),
                    'total_reviews' => $sellerReviews->count() + $tradeReviews->count(),
                ]);
            }
        }

        return back()->with('success', 'Thank you for your review!');
    }

    public function disputeForm($id)
    {
        $trade = Trade::where(function ($q) {
            $q->where('initiator_id', Auth::id())
                ->orWhere('participant_id', Auth::id());
        })
            ->with(['tradeListing', 'initiator', 'participant', 'items'])
            ->findOrFail($id);

        if ($trade->status === 'completed' || $trade->status === 'cancelled' || $trade->status === 'disputed') {
            return redirect()->route('trading.trades.show', $trade->id)
                ->with('error', 'This trade cannot be disputed.');
        }

        return view('trading.trades.dispute-form', compact('trade'));
    }

    public function dispute(Request $request, $id)
    {
        $trade = Trade::where(function ($q) {
            $q->where('initiator_id', Auth::id())
                ->orWhere('participant_id', Auth::id());
        })
            ->findOrFail($id);

        if ($trade->status === 'completed' || $trade->status === 'cancelled') {
            return back()->with('error', 'Cannot dispute a completed or cancelled trade.');
        }

        $validated = $request->validate([
            'type' => 'required|in:not_received,damaged,wrong_item,other',
            'description' => 'required|string|max:2000',
            'evidence_images' => 'nullable|array',
            'evidence_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        DB::beginTransaction();
        try {
            $this->tradeService->updateTradeStatus($trade->id, 'disputed');

            $evidencePaths = [];
            if ($request->hasFile('evidence_images')) {
                foreach ($request->file('evidence_images') as $file) {
                    if ($file->isValid()) {
                        $evidencePaths[] = $file->store('trade-disputes/' . $trade->id, 'public');
                    }
                }
            }

            TradeDispute::create([
                'trade_id' => $trade->id,
                'reporter_id' => Auth::id(),
                'type' => $validated['type'],
                'description' => $validated['description'],
                'evidence_images' => $evidencePaths,
                'status' => 'open',
            ]);

            DB::commit();

            return redirect()->route('trading.trades.show', $trade->id)
                ->with('success', 'Trade dispute opened. A moderator will review shortly.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to open dispute: ' . $e->getMessage());
        }
    }

    public function cancel($id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if ($trade->status === 'completed') {
            return back()->with('error', 'Cannot cancel a completed trade.');
        }

        DB::beginTransaction();
        try {
            // Update product statuses back to available
            foreach ($trade->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)
                        ->update(['trade_status' => 'available_for_trade']);
                } elseif ($item->user_product_id) {
                    UserProduct::where('id', $item->user_product_id)
                        ->update(['status' => 'available']);
                }
            }

            $trade->update(['status' => 'cancelled']);
            $trade->tradeListing->update(['status' => 'active']);

            DB::commit();

            // TODO: Send notification

            return redirect()->route('trading.trades.index')
                ->with('success', 'Trade cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel trade: ' . $e->getMessage());
        }
    }
}
