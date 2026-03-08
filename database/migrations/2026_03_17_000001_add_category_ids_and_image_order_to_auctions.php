<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'category_ids')) {
                $table->json('category_ids')->nullable()->after('category_id');
            }
        });

        if (Schema::hasTable('auction_images') && ! Schema::hasColumn('auction_images', 'display_order')) {
            Schema::table('auction_images', function (Blueprint $table) {
                $table->unsignedInteger('display_order')->default(0)->after('is_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (Schema::hasColumn('auctions', 'category_ids')) {
                $table->dropColumn('category_ids');
            }
        });

        if (Schema::hasTable('auction_images') && Schema::hasColumn('auction_images', 'display_order')) {
            Schema::table('auction_images', function (Blueprint $table) {
                $table->dropColumn('display_order');
            });
        }
    }
};
