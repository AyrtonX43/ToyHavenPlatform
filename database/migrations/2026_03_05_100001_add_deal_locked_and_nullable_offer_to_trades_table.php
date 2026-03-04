<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->foreignId('trade_offer_id')->nullable()->change();
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE trades MODIFY COLUMN status ENUM('pending_shipping', 'shipped', 'received', 'completed', 'disputed', 'cancelled', 'deal_locked') DEFAULT 'pending_shipping'");
        }
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->foreignId('trade_offer_id')->nullable(false)->change();
        });

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            DB::statement("ALTER TABLE trades MODIFY COLUMN status ENUM('pending_shipping', 'shipped', 'received', 'completed', 'disputed', 'cancelled') DEFAULT 'pending_shipping'");
        }
    }
};
