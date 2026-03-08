<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'duration_hours')) {
                $table->unsignedInteger('duration_hours')->nullable()->after('bid_increment');
            }
            if (! Schema::hasColumn('auctions', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('terms_accepted_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn(['duration_hours', 'rejection_reason']);
        });
    }
};
