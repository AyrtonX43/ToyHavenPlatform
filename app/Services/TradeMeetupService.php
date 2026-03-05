<?php

namespace App\Services;

use App\Models\Product;
use App\Models\Trade;
use App\Models\TradeListing;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;

class TradeMeetupService
{
    public function scheduleMeetup(Trade $trade, array $data): Trade
    {
        $trade->update([
            'meetup_location_lat' => $data['meetup_location_lat'] ?? null,
            'meetup_location_lng' => $data['meetup_location_lng'] ?? null,
            'meetup_location_address' => $data['meetup_location_address'] ?? null,
            'meetup_scheduled_at' => $data['meetup_scheduled_at'] ?? null,
            'status' => 'meetup_scheduled',
        ]);

        return $trade->fresh();
    }

    public function confirmMeetup(Trade $trade): Trade
    {
        $trade->update([
            'meetup_completed_at' => now(),
            'status' => 'meetup_completed',
        ]);

        return $trade->fresh();
    }

    public function complete(Trade $trade): Trade
    {
        return DB::transaction(function () use ($trade) {
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

            return $trade->fresh();
        });
    }

    public function cancel(Trade $trade): Trade
    {
        return DB::transaction(function () use ($trade) {
            $trade->update(['status' => 'cancelled']);

            $listing = $trade->tradeListing;
            $listing->update(['status' => 'active']);

            foreach ($trade->items as $item) {
                if ($item->product_id) {
                    Product::where('id', $item->product_id)->update(['trade_status' => 'available_for_trade']);
                } elseif ($item->user_product_id) {
                    UserProduct::where('id', $item->user_product_id)->update(['status' => 'available']);
                }
            }

            return $trade->fresh();
        });
    }
}
