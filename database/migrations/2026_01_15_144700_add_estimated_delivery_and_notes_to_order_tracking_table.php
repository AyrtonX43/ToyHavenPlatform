<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->date('estimated_delivery_date')->nullable()->after('location');
            $table->text('notes')->nullable()->after('estimated_delivery_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_tracking', function (Blueprint $table) {
            $table->dropColumn(['estimated_delivery_date', 'notes']);
        });
    }
};
