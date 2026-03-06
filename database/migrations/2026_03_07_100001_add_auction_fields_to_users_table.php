<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('auction_suspended_until')->nullable()->after('trade_suspended_until');
            $table->unsignedTinyInteger('auction_suspension_offence_count')->default(0)->after('auction_suspended_until');
            $table->boolean('auction_banned')->default(false)->after('auction_suspension_offence_count');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['auction_suspended_until', 'auction_suspension_offence_count', 'auction_banned']);
        });
    }
};
