<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\ModeratorAction;
use App\Models\Trade;
use App\Models\TradeDispute;
use App\Models\Product;
use App\Models\UserProduct;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TradeDisputeController extends Controller
{
    public function index(Request $request)
    {
        $query = TradeDispute::with(['trade.tradeListing', 'trade.initiator', 'trade.participant', 'reporter', 'assignedTo']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $disputes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('moderator.trade-disputes.index', compact('disputes'));
    }

    public function show(TradeDispute $tradeDispute)
    {
        $tradeDispute->load([
            'trade.tradeListing.product.images',
            'trade.tradeListing.userProduct.images',
            'trade.tradeOffer.offeredProduct.images',
            'trade.tradeOffer.offeredUserProduct.images',
            'trade.initiator',
            'trade.participant',
            'trade.items',
            'reporter',
            'assignedTo',
        ]);

        return view('moderator.trade-disputes.show', compact('tradeDispute'));
    }

    public function assign(TradeDispute $tradeDispute)
    {
        if ($tradeDispute->status === 'resolved' || $tradeDispute->status === 'closed') {
            return back()->with('error', 'This dispute has already been resolved.');
        }

        $tradeDispute->update([
            'assigned_to' => auth()->id(),
            'status' => 'investigating',
        ]);

        ModeratorAction::log(
            auth()->id(),
            'trade_dispute_assigned',
            $tradeDispute,
            'Trade dispute assigned to self',
            ['dispute_id' => $tradeDispute->id, 'trade_id' => $tradeDispute->trade_id]
        );

        return back()->with('success', 'Dispute assigned to you.');
    }

    public function resolve(Request $request, TradeDispute $tradeDispute)
    {
        if ($tradeDispute->trade->status !== 'disputed') {
            return back()->with('error', 'The associated trade is not in dispute.');
        }

        $validated = $request->validate([
            'resolution' => 'required|in:completed,cancelled',
            'notes' => 'nullable|string|max:1000',
        ]);

        DB::beginTransaction();
        try {
            $trade = $tradeDispute->trade;

            if ($validated['resolution'] === 'completed') {
                $trade->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);

                foreach ($trade->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->update(['trade_status' => 'traded']);
                    } elseif ($item->user_product_id) {
                        UserProduct::where('id', $item->user_product_id)->update(['status' => 'traded']);
                    }
                }

                $trade->tradeListing->update(['status' => 'completed']);
            } else {
                foreach ($trade->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->update(['trade_status' => 'available_for_trade']);
                    } elseif ($item->user_product_id) {
                        UserProduct::where('id', $item->user_product_id)->update(['status' => 'available']);
                    }
                }

                $trade->update(['status' => 'cancelled']);
                $trade->tradeListing->update(['status' => 'active']);
            }

            $tradeDispute->update([
                'status' => 'resolved',
                'resolution_type' => $validated['resolution'],
                'resolution_notes' => $validated['notes'] ?? null,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
                'assigned_to' => $tradeDispute->assigned_to ?? auth()->id(),
            ]);

            ModeratorAction::log(
                auth()->id(),
                'trade_dispute_resolved',
                $tradeDispute,
                'Trade dispute resolved: ' . $validated['resolution'],
                ['dispute_id' => $tradeDispute->id, 'trade_id' => $trade->id, 'resolution' => $validated['resolution']]
            );

            DB::commit();

            return redirect()->route('moderator.trade-disputes.index')
                ->with('success', 'Dispute resolved successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to resolve dispute: ' . $e->getMessage());
        }
    }
}
