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
            $table->string('payment_method')->nullable(); // qrph, paypal_demo
            $table->string('payment_link')->nullable();
            $table->string('paymongo_payment_intent_id')->nullable();
            $table->string('paypal_transaction_id')->nullable();
            $table->string('receipt_number')->nullable();
            $table->string('receipt_path')->nullable();
            $table->timestamp('receipt_generated_at')->nullable();
            $table->string('seller_paypal_email')->nullable();
            $table->string('winner_proof_path')->nullable();
            $table->timestamp('winner_received_confirmed_at')->nullable();
            $table->timestamp('seller_delivery_confirmed_at')->nullable();
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
            $table->index(['escrow_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_payments');
    }
};
