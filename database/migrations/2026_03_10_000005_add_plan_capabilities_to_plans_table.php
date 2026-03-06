<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->boolean('can_register_individual_seller')->default(false)->after('features');
            $table->boolean('can_register_business_seller')->default(false)->after('can_register_individual_seller');
            $table->boolean('has_analytics_dashboard')->default(false)->after('can_register_business_seller');
        });
    }

    public function down(): void
    {
        Schema::table('plans', function (Blueprint $table) {
            $table->dropColumn(['can_register_individual_seller', 'can_register_business_seller', 'has_analytics_dashboard']);
        });
    }
};
