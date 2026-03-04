<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('moderator_permissions')->nullable()->after('role')
                ->comment('For moderator role: auctions_view, auctions_moderate, auction_reports_view, auction_sellers_view');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moderator_permissions');
        });
    }
};
