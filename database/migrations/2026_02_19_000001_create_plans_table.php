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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->decimal('price', 10, 2);
            $table->enum('interval', ['monthly', 'yearly'])->default('monthly');
            $table->integer('interval_count')->default(1);
            $table->text('description')->nullable();
            $table->json('benefits')->nullable()->comment('early_access_hours, buyers_premium_rate, toyshop_discount, free_shipping_min, etc.');
            $table->json('features')->nullable()->comment('Array of feature strings for display');
            $table->string('paymongo_plan_id')->nullable()->comment('PayMongo plan ID for recurring billing');
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('is_active');
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
