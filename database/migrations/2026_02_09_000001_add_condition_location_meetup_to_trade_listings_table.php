<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_listings', 'condition')) {
                $table->string('condition')->nullable()->after('description');
            }
            if (!Schema::hasColumn('trade_listings', 'location')) {
                $table->string('location')->nullable()->after('condition');
            }
            if (!Schema::hasColumn('trade_listings', 'meet_up_references')) {
                $table->text('meet_up_references')->nullable()->after('location');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trade_listings', function (Blueprint $table) {
            $columns = array_filter(
                ['condition', 'location', 'meet_up_references'],
                fn ($col) => Schema::hasColumn('trade_listings', $col)
            );
            if (!empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
