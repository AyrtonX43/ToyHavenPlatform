<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Roll back business page update requests feature.
     */
    public function up(): void
    {
        if (Schema::hasTable('business_page_update_requests')) {
            Schema::dropIfExists('business_page_update_requests');
        }

        if (Schema::hasColumn('business_page_settings', 'profile_photo_path')) {
            Schema::table('business_page_settings', function (Blueprint $table) {
                $table->dropColumn('profile_photo_path');
            });
        }

        if (Schema::hasColumn('sellers', 'business_email_verified_at')) {
            Schema::table('sellers', function (Blueprint $table) {
                $table->dropColumn('business_email_verified_at');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No reverse - feature was removed
    }
};
