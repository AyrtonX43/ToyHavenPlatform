<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Trade;
use App\Models\TradeDispute;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;

class TradeDisputeService
{
    public function create(Trade $trade, array $data): TradeDispute
    {
        $trade->update(['status' => 'disputed']);

        return TradeDispute::create(array_merge($data, [
            'trade_id' => $trade->id,
            'reporter_id' => auth()->id(),
            'status' => 'open',
        ]));
    }

    public function resolve(TradeDispute $dispute, string $resolution, ?string $notes = null): void
    {
        DB::transaction(function () use ($dispute, $resolution, $notes) {
            $trade = $dispute->trade;

            $dispute->update([
                'status' => 'resolved',
                'resolution_type' => $resolution,
                'resolution_notes' => $notes,
                'resolved_at' => now(),
                'resolved_by' => auth()->id(),
            ]);

            if ($resolution === 'completed') {
                $trade->update([
                    'status' => 'completed',
                    'completed_at' => now(),
                ]);
                $trade->tradeListing->update(['status' => 'completed']);
                foreach ($trade->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->update(['trade_status' => 'traded']);
                    } elseif ($item->user_product_id) {
                        UserProduct::where('id', $item->user_product_id)->update(['status' => 'traded']);
                    }
                }
            } else {
                $trade->update(['status' => 'cancelled']);
                $trade->tradeListing->update(['status' => 'active']);
                foreach ($trade->items as $item) {
                    if ($item->product_id) {
                        Product::where('id', $item->product_id)->update(['trade_status' => 'available_for_trade']);
                    } elseif ($item->user_product_id) {
                        UserProduct::where('id', $item->user_product_id)->update(['status' => 'available']);
                    }
                }
            }
        });
    }
}
