<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->timestamp('receipt_confirmed_at')->nullable()->after('delivered_at');
            $table->boolean('has_dispute')->default(false)->after('receipt_confirmed_at');
            $table->string('courier_name')->nullable()->after('tracking_number');
            $table->timestamp('cancelled_at')->nullable()->after('has_dispute');
            $table->text('cancellation_reason')->nullable()->after('cancelled_at');
            $table->foreignId('cancelled_by')->nullable()->constrained('users')->onDelete('set null')->after('cancellation_reason');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropForeign(['cancelled_by']);
            $table->dropColumn([
                'receipt_confirmed_at',
                'has_dispute',
                'courier_name',
                'cancelled_at',
                'cancellation_reason',
                'cancelled_by'
            ]);
        });
    }
};
