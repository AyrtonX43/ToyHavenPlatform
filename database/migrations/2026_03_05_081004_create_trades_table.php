<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_listing_id')->constrained('trade_listings')->onDelete('cascade');
            $table->foreignId('trade_offer_id')->nullable()->constrained('trade_offers')->onDelete('set null');
            $table->foreignId('initiator_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('initiator_seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->foreignId('participant_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('participant_seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->enum('status', ['pending_meetup', 'meetup_scheduled', 'meetup_completed', 'completed', 'disputed', 'cancelled'])->default('pending_meetup');
            $table->decimal('meetup_location_lat', 10, 7)->nullable();
            $table->decimal('meetup_location_lng', 10, 7)->nullable();
            $table->string('meetup_location_address')->nullable();
            $table->timestamp('meetup_scheduled_at')->nullable();
            $table->timestamp('meetup_completed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
