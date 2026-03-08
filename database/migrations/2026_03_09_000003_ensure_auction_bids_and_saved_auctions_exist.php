<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auction_bids')) {
            Schema::create('auction_bids', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auction_id')->constrained()->onDelete('cascade');
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->decimal('amount', 12, 2);
                $table->integer('rank_at_bid')->nullable();
                $table->boolean('is_winning')->default(false);
                $table->timestamps();
                $table->index(['auction_id', 'amount']);
            });
        }

        if (! Schema::hasTable('saved_auctions')) {
            Schema::create('saved_auctions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('auction_id')->constrained()->onDelete('cascade');
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();
                $table->unique(['user_id', 'auction_id']);
            });
        }
    }

    public function down(): void
    {
        // Do not drop - other code may depend on these
    }
};
