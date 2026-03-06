<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_payments', 'payment_method')) {
                $table->string('payment_method')->nullable()->after('paymongo_payment_id');
            }
            if (! Schema::hasColumn('subscription_payments', 'paypal_order_id')) {
                $table->string('paypal_order_id')->nullable()->after('payment_method');
            }
        });
    }

    public function down(): void
    {
        Schema::table('subscription_payments', function (Blueprint $table) {
            $table->dropColumn(['payment_method', 'paypal_order_id']);
        });
    }
};
