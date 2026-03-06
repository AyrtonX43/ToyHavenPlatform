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
            $table->decimal('amount', 12, 2);
            $table->string('payment_method')->nullable()->comment('qrph, paypal');
            $table->string('payment_reference')->nullable();
            $table->string('status')->default('pending')->comment('pending, paid, held, released, refunded');
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('payment_deadline')->nullable();
            $table->string('receipt_path')->nullable();
            $table->string('delivery_status')->nullable()->comment('pending_shipment, shipped, delivered, confirmed');
            $table->timestamp('confirmed_at')->nullable();
            $table->boolean('is_second_chance')->default(false);
            $table->timestamps();

            $table->index(['status', 'payment_deadline']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_payments');
    }
};
