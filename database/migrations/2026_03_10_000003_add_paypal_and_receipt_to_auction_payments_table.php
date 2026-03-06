<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_payments', function (Blueprint $table) {
            $table->string('payment_method')->nullable()->after('paymongo_payment_intent_id');
            $table->string('paypal_order_id')->nullable()->after('payment_method');
            $table->string('receipt_number')->nullable()->after('released_at');
            $table->string('receipt_path')->nullable()->after('receipt_number');
            $table->timestamp('receipt_generated_at')->nullable()->after('receipt_path');
        });
    }

    public function down(): void
    {
        Schema::table('auction_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'paypal_order_id', 'receipt_number', 'receipt_path', 'receipt_generated_at']);
        });
    }
};
