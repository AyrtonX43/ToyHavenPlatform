<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('conversations', 'trade_id')) {
                $table->foreignId('trade_id')->nullable()->after('user2_id')->constrained('trades')->onDelete('cascade');
            }
            if (! Schema::hasColumn('conversations', 'trade_listing_id')) {
                $table->foreignId('trade_listing_id')->nullable()->after('trade_id')->constrained('trade_listings')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['trade_id']);
            $table->dropForeign(['trade_listing_id']);
            $table->dropColumn(['trade_id', 'trade_listing_id']);
        });
    }
};
