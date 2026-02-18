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
        Schema::create('seller_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('order_id')->nullable()->constrained()->onDelete('set null');
            $table->integer('overall_rating')->comment('1-5 stars');
            $table->integer('product_quality_rating')->nullable()->comment('1-5 stars');
            $table->integer('shipping_rating')->nullable()->comment('1-5 stars');
            $table->integer('communication_rating')->nullable()->comment('1-5 stars');
            $table->text('review_text')->nullable();
            $table->timestamps();
            
            $table->unique(['seller_id', 'user_id', 'order_id'], 'seller_user_order_review_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_reviews');
    }
};
