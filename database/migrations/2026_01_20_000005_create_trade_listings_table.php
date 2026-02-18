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
        Schema::create('trade_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('user_product_id')->nullable()->constrained('user_products')->onDelete('set null');
            $table->string('title');
            $table->text('description');
            $table->enum('trade_type', ['barter', 'barter_with_cash', 'listing'])->default('barter');
            $table->json('desired_items')->nullable()->comment('Categories, conditions, etc. they want in return');
            $table->decimal('cash_difference', 10, 2)->nullable()->comment('Cash amount willing to add/receive');
            $table->enum('status', ['active', 'pending_trade', 'completed', 'cancelled', 'expired'])->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('offers_count')->default(0);
            $table->timestamps();
            
            $table->index('status');
            $table->index('trade_type');
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_listings');
    }
};
