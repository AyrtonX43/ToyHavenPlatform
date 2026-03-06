<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'auction_offense_count')) {
                $table->unsignedInteger('auction_offense_count')->default(0)->after('trade_suspended_until');
            }
            if (! Schema::hasColumn('users', 'auction_suspended_until')) {
                $after = Schema::hasColumn('users', 'auction_offense_count') ? 'auction_offense_count' : 'trade_suspended_until';
                $table->timestamp('auction_suspended_until')->nullable()->after($after);
            }
            if (! Schema::hasColumn('users', 'auction_banned_at')) {
                $table->timestamp('auction_banned_at')->nullable()->after('auction_suspended_until');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auction_offense_count', 'auction_suspended_until', 'auction_banned_at']);
        });
    }
};
