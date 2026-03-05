<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->foreignId('user_product_id')->nullable()->constrained('user_products')->onDelete('set null');
            $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
            $table->string('title');
            $table->string('brand')->nullable();
            $table->text('description');
            $table->string('condition', 50)->nullable();
            $table->string('location')->nullable();
            $table->decimal('location_lat', 10, 7)->nullable();
            $table->decimal('location_lng', 10, 7)->nullable();
            $table->text('meet_up_references')->nullable();
            $table->enum('trade_type', ['exchange', 'exchange_with_cash', 'cash'])->default('exchange');
            $table->json('desired_items')->nullable();
            $table->decimal('cash_amount', 10, 2)->nullable();
            $table->enum('status', ['pending_approval', 'active', 'pending_deal', 'completed', 'cancelled', 'rejected', 'expired'])->default('pending_approval');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->integer('views_count')->default(0);
            $table->integer('offers_count')->default(0);
            $table->timestamps();

            $table->index('status');
            $table->index('trade_type');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_listings');
    }
};
