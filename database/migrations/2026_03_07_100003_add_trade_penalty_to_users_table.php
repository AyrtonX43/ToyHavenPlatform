<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('trade_penalty_count')->default(0)->after('trade_suspended_by');
            $table->timestamp('trade_suspended_until')->nullable()->after('trade_penalty_count');
            $table->boolean('trade_banned')->default(false)->after('trade_suspended_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trade_penalty_count', 'trade_suspended_until', 'trade_banned']);
        });
    }
};
