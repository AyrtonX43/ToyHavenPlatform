<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_payment_id')->constrained('auction_payments')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('auction_id')->constrained()->onDelete('cascade');
            $table->foreignId('auction_seller_profile_id')->constrained('auction_seller_profiles')->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('feedback')->nullable();
            $table->boolean('for_listing')->default(true); // true = listing review, false = seller review
            $table->timestamps();

            $table->unique(['auction_payment_id', 'for_listing']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_reviews');
    }
};
