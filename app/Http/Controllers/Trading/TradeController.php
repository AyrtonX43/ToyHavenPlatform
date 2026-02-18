<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Trade;
use App\Models\Product;
use App\Models\UserProduct;
use App\Services\TradeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
                'conversation',
            ])
            ->findOrFail($id);

        $isInitiator = $trade->isInitiator(Auth::id());
        $isParticipant = $trade->isParticipant(Auth::id());
        $otherParty = $trade->getOtherParty(Auth::id());

        return view('trading.trades.show', compact('trade', 'isInitiator', 'isParticipant', 'otherParty'));
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

    public function markReceived($id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if ($trade->status !== 'shipped' && $trade->status !== 'received') {
            return back()->with('error', 'Invalid trade status for receiving.');
        }

        DB::beginTransaction();
        try {
            if ($trade->isInitiator(Auth::id())) {
                $trade->update(['initiator_received_at' => now()]);
            } else {
                $trade->update(['participant_received_at' => now()]);
            }

            // Check if both parties have received
            if ($trade->initiator_received_at && $trade->participant_received_at) {
                $trade->update(['status' => 'received']);
            } else {
                $trade->update(['status' => 'received']);
            }

            DB::commit();

            // TODO: Send notification

            return back()->with('success', 'Item marked as received!');
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

    public function dispute($id)
    {
        $trade = Trade::where(function($q) {
                $q->where('initiator_id', Auth::id())
                  ->orWhere('participant_id', Auth::id());
            })
            ->findOrFail($id);

        if ($trade->status === 'completed' || $trade->status === 'cancelled') {
            return back()->with('error', 'Cannot dispute a completed or cancelled trade.');
        }

        try {
            $this->tradeService->updateTradeStatus($trade->id, 'disputed');
            // TODO: Create report or notification for admin
            return back()->with('success', 'Trade dispute opened. Admin will review.');
        } catch (\Exception $e) {
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
