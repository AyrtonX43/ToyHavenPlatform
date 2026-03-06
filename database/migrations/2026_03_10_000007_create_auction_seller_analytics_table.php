<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_seller_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('period')->comment('e.g. 2026-03 for monthly');
            $table->decimal('total_revenue', 12, 2)->default(0);
            $table->unsignedInteger('total_listings')->default(0);
            $table->unsignedInteger('total_sold')->default(0);
            $table->unsignedInteger('total_bids_received')->default(0);
            $table->timestamps();

            $table->unique(['user_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_seller_analytics');
    }
};
