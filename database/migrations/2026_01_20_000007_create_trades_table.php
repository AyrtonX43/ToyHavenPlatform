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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_listing_id')->constrained('trade_listings')->onDelete('cascade');
            $table->foreignId('trade_offer_id')->constrained('trade_offers')->onDelete('cascade');
            $table->foreignId('initiator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('initiator_seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('participant_seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->decimal('cash_amount', 10, 2)->nullable()->comment('Final cash difference');
            $table->enum('status', ['pending_shipping', 'shipped', 'received', 'completed', 'disputed', 'cancelled'])->default('pending_shipping');
            $table->json('initiator_shipping_address')->nullable();
            $table->json('participant_shipping_address')->nullable();
            $table->string('initiator_tracking_number')->nullable();
            $table->string('participant_tracking_number')->nullable();
            $table->timestamp('initiator_shipped_at')->nullable();
            $table->timestamp('participant_shipped_at')->nullable();
            $table->timestamp('initiator_received_at')->nullable();
            $table->timestamp('participant_received_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
