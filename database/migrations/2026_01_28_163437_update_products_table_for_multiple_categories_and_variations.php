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
            // Remove condition field
            $table->dropColumn('condition');
            
            // Add video URL field
            $table->string('video_url', 500)->nullable()->after('description');
            
            // Add platform fee and tax fields
            $table->decimal('platform_fee_percentage', 5, 2)->default(5.00)->after('amazon_reference_price');
            $table->decimal('tax_percentage', 5, 2)->default(12.00)->after('platform_fee_percentage');
            $table->decimal('final_price', 10, 2)->nullable()->after('tax_percentage');
            
            // Make category_id nullable (we'll use pivot table for multiple categories)
            // But keep it for backward compatibility as primary category
            // $table->foreignId('category_id')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Remove new fields
            $table->dropColumn(['video_url', 'platform_fee_percentage', 'tax_percentage', 'final_price']);
        });
        
        // Re-add condition field
        if (!Schema::hasColumn('products', 'condition')) {
            Schema::table('products', function (Blueprint $table) {
                $table->enum('condition', ['new', 'used', 'refurbished'])->default('new')->after('stock_quantity');
            });
        }
    }
};
