<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained('trades')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('user_product_id')->nullable()->constrained('user_products')->onDelete('set null');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->string('product_name');
            $table->text('product_description')->nullable();
            $table->json('product_images')->nullable();
            $table->string('product_condition', 50)->default('used');
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->enum('side', ['initiator', 'participant']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_items');
    }
};
