<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Drop old trade system tables and foreign key columns.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (Schema::hasColumn('conversations', 'trade_id')) {
                $table->dropConstrainedForeignId('trade_id');
            }
            if (Schema::hasColumn('conversations', 'trade_listing_id')) {
                $table->dropConstrainedForeignId('trade_listing_id');
            }
        });

        Schema::table('messages', function (Blueprint $table) {
            if (Schema::hasColumn('messages', 'trade_listing_id')) {
                $table->dropConstrainedForeignId('trade_listing_id');
            }
        });

        Schema::dropIfExists('trade_reviews');
        Schema::dropIfExists('trade_disputes');
        Schema::dropIfExists('trade_items');
        Schema::dropIfExists('trades');
        Schema::dropIfExists('trade_offers');
        Schema::dropIfExists('trade_listing_images');
        Schema::dropIfExists('trade_listings');
    }

    /**
     * Reverse: recreate tables is not implemented (new schema will be added by separate migration).
     */
    public function down(): void
    {
        // Tables will be recreated by the new trade schema migration
    }
};
