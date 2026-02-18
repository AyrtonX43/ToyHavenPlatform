<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE trade_listings MODIFY COLUMN status ENUM(
            'active',
            'pending_trade',
            'completed',
            'cancelled',
            'expired',
            'pending_approval',
            'rejected'
        ) NOT NULL DEFAULT 'active'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE trade_listings MODIFY COLUMN status ENUM(
            'active',
            'pending_trade',
            'completed',
            'cancelled',
            'expired'
        ) NOT NULL DEFAULT 'active'");
    }
};
