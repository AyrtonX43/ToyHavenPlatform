<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add 'cash' to enum, then migrate data, then remove 'listing'
        DB::statement("ALTER TABLE trade_listings MODIFY COLUMN trade_type ENUM('barter', 'barter_with_cash', 'listing', 'cash') NOT NULL DEFAULT 'barter'");
        DB::table('trade_listings')->where('trade_type', 'listing')->update(['trade_type' => 'cash']);
        DB::statement("ALTER TABLE trade_listings MODIFY COLUMN trade_type ENUM('barter', 'barter_with_cash', 'cash') NOT NULL DEFAULT 'barter'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE trade_listings MODIFY COLUMN trade_type ENUM('barter', 'barter_with_cash', 'listing', 'cash') NOT NULL DEFAULT 'barter'");
        DB::table('trade_listings')->where('trade_type', 'cash')->update(['trade_type' => 'listing']);
        DB::statement("ALTER TABLE trade_listings MODIFY COLUMN trade_type ENUM('barter', 'barter_with_cash', 'listing') NOT NULL DEFAULT 'barter'");
    }
};
