<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_bids', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 12, 2);
            $table->string('anonymous_display_id')->nullable(); // e.g. Bidder #A1B2C3
            $table->boolean('is_winning')->default(false);
            $table->timestamps();

            $table->index(['auction_id', 'amount']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_bids');
    }
};
