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
        Schema::create('trade_offers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_listing_id')->constrained('trade_listings')->onDelete('cascade');
            $table->foreignId('offerer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('offerer_seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->foreignId('offered_product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('offered_user_product_id')->nullable()->constrained('user_products')->onDelete('set null');
            $table->decimal('cash_amount', 10, 2)->nullable()->comment('Cash difference being offered');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'counter_offered', 'withdrawn'])->default('pending');
            $table->foreignId('counter_offer_id')->nullable()->constrained('trade_offers')->onDelete('set null');
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_offers');
    }
};
