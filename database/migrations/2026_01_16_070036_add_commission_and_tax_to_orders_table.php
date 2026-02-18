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
        Schema::table('orders', function (Blueprint $table) {
            $table->decimal('admin_commission', 10, 2)->default(0)->after('total_amount');
            $table->decimal('admin_commission_rate', 5, 2)->default(5.0)->after('admin_commission');
            $table->decimal('tax_amount', 10, 2)->default(0)->after('admin_commission_rate');
            $table->decimal('tax_rate', 5, 2)->default(12.0)->after('tax_amount');
            $table->decimal('transaction_fee', 10, 2)->default(0)->after('tax_rate');
            $table->decimal('seller_earnings', 10, 2)->default(0)->after('transaction_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn([
                'admin_commission',
                'admin_commission_rate',
                'tax_amount',
                'tax_rate',
                'transaction_fee',
                'seller_earnings',
            ]);
        });
    }
};
