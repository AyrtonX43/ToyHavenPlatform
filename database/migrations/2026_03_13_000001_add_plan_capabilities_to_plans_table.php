<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            if (! Schema::hasColumn('plans', 'can_register_individual_seller')) {
                $table->boolean('can_register_individual_seller')->default(false)->after('is_active');
            }
            if (! Schema::hasColumn('plans', 'can_register_business_seller')) {
                $table->boolean('can_register_business_seller')->default(false)->after('can_register_individual_seller');
            }
            if (! Schema::hasColumn('plans', 'has_analytics_dashboard')) {
                $table->boolean('has_analytics_dashboard')->default(false)->after('can_register_business_seller');
            }
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $columns = ['can_register_individual_seller', 'can_register_business_seller', 'has_analytics_dashboard'];
            foreach ($columns as $col) {
                if (Schema::hasColumn('plans', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
