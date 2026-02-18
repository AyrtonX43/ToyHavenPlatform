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
        Schema::create('order_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->onDelete('cascade');
            $table->enum('status', ['order_placed', 'payment_confirmed', 'processing', 'packed', 'shipped', 'in_transit', 'out_for_delivery', 'delivered']);
            $table->text('description')->nullable();
            $table->string('tracking_number')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null')->comment('Seller ID or system');
            $table->string('location')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_tracking');
    }
};
