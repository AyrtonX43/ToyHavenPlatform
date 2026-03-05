<?php

namespace App\Services;

use App\Models\Product;
use App\Models\TradeListing;
use App\Models\UserProduct;
use Illuminate\Support\Facades\DB;

class TradeListingService
{
    public function create(array $data): TradeListing
    {
        return DB::transaction(function () use ($data) {
            $listing = TradeListing::create($data);

            if ($listing->product_id) {
                Product::where('id', $listing->product_id)->update(['trade_status' => 'in_trade']);
            } elseif ($listing->user_product_id) {
                UserProduct::where('id', $listing->user_product_id)->update(['status' => 'in_trade']);
            }

            return $listing;
        });
    }

    public function update(TradeListing $listing, array $data): TradeListing
    {
        return DB::transaction(function () use ($listing, $data) {
            $listing->update($data);
            return $listing->fresh();
        });
    }

    public function markCompleted(TradeListing $listing): void
    {
        DB::transaction(function () use ($listing) {
            $listing->update(['status' => 'completed']);

            if ($listing->product_id) {
                Product::where('id', $listing->product_id)->update(['trade_status' => 'traded']);
            } elseif ($listing->user_product_id) {
                UserProduct::where('id', $listing->user_product_id)->update(['status' => 'traded']);
            }
        });
    }

    public function returnProductToAvailable(TradeListing $listing): void
    {
        if ($listing->product_id) {
            Product::where('id', $listing->product_id)->update(['trade_status' => 'available_for_trade']);
        } elseif ($listing->user_product_id) {
            UserProduct::where('id', $listing->user_product_id)->update(['status' => 'available']);
        }
    }
}
