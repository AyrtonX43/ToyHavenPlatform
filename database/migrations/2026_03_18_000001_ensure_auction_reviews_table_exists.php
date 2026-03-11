<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auction_reviews')) {
            if (! Schema::hasColumn('auction_reviews', 'photos')) {
                Schema::table('auction_reviews', function (Blueprint $table) {
                    $table->json('photos')->nullable();
                });
            }
            return;
        }

        if (! Schema::hasTable('auction_payments') || ! Schema::hasTable('auctions')) {
            return;
        }

        Schema::create('auction_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_payment_id')->constrained('auction_payments')->onDelete('cascade');
            $table->foreignId('winner_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('auction_id')->constrained('auctions')->onDelete('cascade');
            $table->foreignId('seller_user_id')->constrained('users')->onDelete('cascade');
            $table->unsignedTinyInteger('rating');
            $table->text('feedback')->nullable();
            $table->json('photos')->nullable();
            $table->timestamp('delivery_confirmed_at')->nullable();
            $table->timestamps();
            $table->unique('auction_payment_id');
            $table->index(['auction_id', 'winner_id']);
        });
    }

    public function down(): void {}
};
