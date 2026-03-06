<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_second_chances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('bid_amount', 12, 2);
            $table->unsignedSmallInteger('queue_position')->default(1);
            $table->enum('status', ['waiting', 'offered', 'accepted', 'expired', 'declined'])->default('waiting');
            $table->string('payment_link')->nullable();
            $table->timestamp('offered_at')->nullable();
            $table->timestamp('deadline')->nullable();
            $table->timestamps();

            $table->index(['auction_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_second_chances');
    }
};
