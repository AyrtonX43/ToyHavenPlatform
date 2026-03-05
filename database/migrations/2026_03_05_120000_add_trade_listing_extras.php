<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_listings', function (Blueprint $table) {
            $table->json('category_ids')->nullable()->after('category_id');
            $table->decimal('meetup_radius_km', 6, 2)->nullable()->after('location_lng');
        });

        Schema::table('trade_listing_images', function (Blueprint $table) {
            $table->boolean('is_thumbnail')->default(false)->after('display_order');
        });
    }

    public function down(): void
    {
        Schema::table('trade_listings', function (Blueprint $table) {
            $table->dropColumn(['category_ids', 'meetup_radius_km']);
        });
        Schema::table('trade_listing_images', function (Blueprint $table) {
            $table->dropColumn('is_thumbnail');
        });
    }
};
