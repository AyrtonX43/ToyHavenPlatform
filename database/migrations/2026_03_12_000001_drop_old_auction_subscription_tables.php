<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Remove FKs from reports BEFORE dropping tables (reports references auction_payments, auctions)
        if (Schema::hasTable('reports')) {
            Schema::table('reports', function (Blueprint $table) {
                if (Schema::hasColumn('reports', 'auction_id')) {
                    $table->dropForeign(['auction_id']);
                    $table->dropColumn('auction_id');
                }
                if (Schema::hasColumn('reports', 'auction_payment_id')) {
                    $table->dropForeign(['auction_payment_id']);
                    $table->dropColumn('auction_payment_id');
                }
            });
        }

        // Remove from orders
        if (Schema::hasTable('orders') && Schema::hasColumn('orders', 'membership_discount_saved')) {
            Schema::table('orders', function (Blueprint $table) {
                $table->dropColumn('membership_discount_saved');
            });
        }

        // Remove auction_alias from users
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'auction_alias')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('auction_alias');
            });
        }

        // Drop tables in correct FK order (child tables first)
        Schema::dropIfExists('auction_reviews');
        Schema::dropIfExists('auction_bids');
        Schema::dropIfExists('auction_images');
        Schema::dropIfExists('auction_second_chances');
        Schema::dropIfExists('auction_payments');
        Schema::dropIfExists('auction_seller_documents');
        Schema::dropIfExists('auction_seller_verifications');
        Schema::dropIfExists('auction_category');
        Schema::dropIfExists('saved_auctions');
        Schema::dropIfExists('auctions');
        Schema::dropIfExists('subscription_payments');
        Schema::dropIfExists('subscriptions');
        Schema::dropIfExists('plans');
        Schema::dropIfExists('wallet_transactions');
        Schema::dropIfExists('wallets');
    }

    public function down(): void
    {
        // Cannot restore - run fresh migrations
    }
};
