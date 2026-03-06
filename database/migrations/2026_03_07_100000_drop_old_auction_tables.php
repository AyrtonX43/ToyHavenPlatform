<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop foreign keys from reports first
        if (Schema::hasTable('reports')) {
            if (Schema::hasColumn('reports', 'auction_id')) {
                Schema::table('reports', function (Blueprint $table) {
                    $table->dropForeign(['auction_id']);
                    $table->dropColumn('auction_id');
                });
            }
            if (Schema::hasColumn('reports', 'auction_payment_id')) {
                Schema::table('reports', function (Blueprint $table) {
                    $table->dropForeign(['auction_payment_id']);
                    $table->dropColumn('auction_payment_id');
                });
            }
        }

        Schema::dropIfExists('auction_second_chances');
        Schema::dropIfExists('auction_payments');
        Schema::dropIfExists('auction_bids');
        Schema::dropIfExists('auction_images');
        Schema::dropIfExists('auction_category');
        Schema::dropIfExists('auctions');
        Schema::dropIfExists('auction_seller_documents');
        Schema::dropIfExists('auction_seller_verifications');
    }

    public function down(): void
    {
        // Cannot restore - old structure removed
    }
};
