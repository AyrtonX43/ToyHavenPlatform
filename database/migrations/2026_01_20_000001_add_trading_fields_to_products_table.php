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
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->after('seller_id')->constrained('users')->onDelete('cascade');
            $table->boolean('is_tradeable')->default(false)->after('status');
            $table->enum('trade_status', ['not_for_trade', 'available_for_trade', 'in_trade', 'traded'])->nullable()->after('is_tradeable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['user_id', 'is_tradeable', 'trade_status']);
        });
    }
};
