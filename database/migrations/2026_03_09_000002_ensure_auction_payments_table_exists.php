<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auction_payments')) {
            Schema::create('auction_payments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auction_id')->constrained()->onDelete('cascade');
                $table->foreignId('winner_id')->constrained('users')->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->string('payment_method')->nullable();
                $table->string('payment_reference')->nullable();
                $table->string('status')->default('pending');
                $table->timestamp('paid_at')->nullable();
                $table->timestamp('payment_deadline')->nullable();
                $table->string('receipt_path')->nullable();
                $table->string('delivery_status')->nullable();
                $table->string('tracking_number')->nullable();
                $table->timestamp('confirmed_at')->nullable();
                $table->boolean('is_second_chance')->default(false);
                $table->timestamps();
                $table->index(['status', 'payment_deadline']);
            });
        } elseif (Schema::hasTable('auction_payments') && ! Schema::hasColumn('auction_payments', 'tracking_number')) {
            Schema::table('auction_payments', function (Blueprint $table) {
                $table->string('tracking_number')->nullable()->after('delivery_status');
            });
        }
    }

    public function down(): void
    {
        // Do not drop - other migrations may depend on it
    }
};
