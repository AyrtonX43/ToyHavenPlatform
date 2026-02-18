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
        Schema::create('product_variations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            $table->string('variation_type'); // e.g., 'size', 'color', 'style'
            $table->string('variation_value'); // e.g., 'Large', 'Red', 'Classic'
            $table->string('sku')->nullable(); // Optional SKU for this variation
            $table->decimal('price_adjustment', 10, 2)->default(0.00); // Price difference from base price
            $table->integer('stock_quantity')->default(0);
            $table->boolean('is_available')->default(true);
            $table->integer('display_order')->default(0);
            $table->timestamps();
            
            // Index for faster lookups
            $table->index(['product_id', 'variation_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variations');
    }
};
