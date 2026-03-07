<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        Schema::table('subscription_payments', function (Blueprint $table) {
            if (! Schema::hasColumn('subscription_payments', 'receipt_number')) {
                $table->string('receipt_number')->nullable()->after('paid_at');
            }
            if (! Schema::hasColumn('subscription_payments', 'receipt_path')) {
                $col = Schema::hasColumn('subscription_payments', 'receipt_number') ? 'receipt_number' : 'paid_at';
                $table->string('receipt_path')->nullable()->after($col);
            }
            if (! Schema::hasColumn('subscription_payments', 'receipt_generated_at')) {
                $table->timestamp('receipt_generated_at')->nullable()->after('receipt_path');
            }
        });
    }

    public function down(): void
    {
        // Do not drop - other migrations handle schema
    }
};
