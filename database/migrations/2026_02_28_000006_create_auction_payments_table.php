<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->onDelete('cascade');
            $table->foreignId('winner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_user_id')->constrained('users')->onDelete('cascade');
            $table->decimal('bid_amount', 12, 2);
            $table->decimal('buyer_premium', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->decimal('seller_payout', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->enum('escrow_status', ['awaiting_payment', 'held', 'released', 'disputed', 'refunded'])->default('awaiting_payment');
            $table->string('payment_link')->nullable();
            $table->string('paymongo_payment_intent_id')->nullable();
            $table->string('tracking_number')->nullable();
            $table->enum('delivery_status', ['pending_shipment', 'shipped', 'delivered', 'confirmed'])->nullable();
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->boolean('is_second_chance')->default(false);
            $table->timestamps();

            $table->index(['payment_status', 'payment_deadline']);
            $table->index(['escrow_status', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_payments');
    }
};
