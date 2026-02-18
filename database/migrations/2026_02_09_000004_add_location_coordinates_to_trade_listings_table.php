<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_listings', 'location_lat')) {
                $table->decimal('location_lat', 10, 8)->nullable()->after('location');
            }
            if (!Schema::hasColumn('trade_listings', 'location_lng')) {
                $table->decimal('location_lng', 11, 8)->nullable()->after('location_lat');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trade_listings', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('trade_listings', 'location_lat')) $cols[] = 'location_lat';
            if (Schema::hasColumn('trade_listings', 'location_lng')) $cols[] = 'location_lng';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
