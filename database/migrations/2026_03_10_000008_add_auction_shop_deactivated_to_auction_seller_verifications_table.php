<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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
