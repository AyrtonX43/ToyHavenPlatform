<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_offers', function (Blueprint $table) {
            $table->foreignId('offered_trade_listing_id')->nullable()->after('offered_user_product_id')
                ->constrained('trade_listings')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('trade_offers', function (Blueprint $table) {
            $table->dropForeign(['offered_trade_listing_id']);
        });
    }
};
