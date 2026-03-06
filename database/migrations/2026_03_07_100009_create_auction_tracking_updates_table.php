<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_tracking_updates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_payment_id')->constrained('auction_payments')->onDelete('cascade');
            $table->string('tracking_number');
            $table->string('carrier')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('updated_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            $table->index('auction_payment_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_tracking_updates');
    }
};
