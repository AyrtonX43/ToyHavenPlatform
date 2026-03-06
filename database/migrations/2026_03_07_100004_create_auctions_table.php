<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auctions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('auction_seller_profile_id')->constrained('auction_seller_profiles')->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('starting_bid', 12, 2);
            $table->decimal('reserve_price', 12, 2)->nullable();
            $table->decimal('bid_increment', 12, 2)->default(1);
            $table->json('allowed_bidder_plan_ids')->nullable(); // null = all plans
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'live', 'ended', 'cancelled'])->default('draft');
            $table->timestamp('start_at')->nullable();
            $table->timestamp('end_at')->nullable();
            $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
            $table->decimal('winning_amount', 12, 2)->nullable();
            $table->unsignedInteger('bids_count')->default(0);
            $table->unsignedInteger('views_count')->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['status', 'end_at']);
            $table->index('auction_seller_profile_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auctions');
    }
};
