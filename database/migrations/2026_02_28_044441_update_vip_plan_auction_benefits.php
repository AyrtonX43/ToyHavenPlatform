<?php

use App\Models\Plan;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $vip = Plan::where('slug', 'vip')->first();

        if (! $vip) {
            return;
        }

        $benefits = $vip->benefits ?? [];

        if (! empty($benefits['can_create_auction'])) {
            return;
        }

        $benefits['can_create_auction'] = true;
        $benefits['max_active_auctions'] = $benefits['max_active_auctions'] ?? 5;
        $benefits['auction_listing_fee'] = $benefits['auction_listing_fee'] ?? 0;

        $vip->update(['benefits' => $benefits]);
    }

    public function down(): void
    {
        $vip = Plan::where('slug', 'vip')->first();

        if (! $vip) {
            return;
        }

        $benefits = $vip->benefits ?? [];
        unset($benefits['can_create_auction'], $benefits['max_active_auctions'], $benefits['auction_listing_fee']);

        $vip->update(['benefits' => $benefits]);
    }
};
