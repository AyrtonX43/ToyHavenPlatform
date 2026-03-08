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
        if (Schema::hasColumn('auction_seller_verifications', 'business_info')) {
            return;
        }

        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->json('business_info')->nullable()->after('type');
        });
    }

    public function down(): void
    {
        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->dropColumn('business_info');
        });
    }
};
