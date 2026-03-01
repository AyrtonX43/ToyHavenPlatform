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
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('region')->nullable()->after('address');
            $table->string('barangay')->nullable()->after('city');
            $table->string('facebook_url')->nullable()->after('postal_code');
            $table->string('instagram_url')->nullable()->after('facebook_url');
            $table->string('tiktok_url')->nullable()->after('instagram_url');
            $table->string('website_url')->nullable()->after('tiktok_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['region', 'barangay', 'facebook_url', 'instagram_url', 'tiktok_url', 'website_url']);
        });
    }
};
