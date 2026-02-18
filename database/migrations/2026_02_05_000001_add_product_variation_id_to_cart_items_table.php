<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            if (! Schema::hasColumn('cart_items', 'product_variation_id')) {
                $table->foreignId('product_variation_id')->nullable()->after('product_id')->constrained('product_variations')->nullOnDelete();
            }
        });

        Schema::table('cart_items', function (Blueprint $table) {
            if (! $this->indexExists('user_product_variation_unique')) {
                $table->unique(['user_id', 'product_id', 'product_variation_id'], 'user_product_variation_unique');
            }
            if (! $this->indexExists('session_product_variation_unique')) {
                $table->unique(['session_id', 'product_id', 'product_variation_id'], 'session_product_variation_unique');
            }
        });
        Schema::table('cart_items', function (Blueprint $table) {
            if ($this->indexExists('user_product_unique')) {
                $table->dropUnique('user_product_unique');
            }
            if ($this->indexExists('session_product_unique')) {
                $table->dropUnique('session_product_unique');
            }
        });
    }

    private function indexExists(string $index): bool
    {
        return count(DB::select('SHOW INDEX FROM cart_items WHERE Key_name = ?', [$index])) > 0;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropUnique('user_product_variation_unique');
            $table->dropUnique('session_product_variation_unique');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->unique(['user_id', 'product_id'], 'user_product_unique');
            $table->unique(['session_id', 'product_id'], 'session_product_unique');
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropForeign(['product_variation_id']);
        });
    }
};
