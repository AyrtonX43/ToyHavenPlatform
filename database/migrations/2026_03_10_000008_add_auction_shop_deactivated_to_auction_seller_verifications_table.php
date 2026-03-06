<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auction_seller_verifications')) {
            return;
        }
        if (Schema::hasColumn('auction_seller_verifications', 'auction_shop_deactivated_at')) {
            return;
        }

        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->timestamp('auction_shop_deactivated_at')->nullable()->after('verified_at');
        });
    }

    public function down(): void
    {
        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->dropColumn('auction_shop_deactivated_at');
        });
    }
};
