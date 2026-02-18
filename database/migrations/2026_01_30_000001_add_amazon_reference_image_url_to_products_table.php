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
            if (!Schema::hasColumn('products', 'amazon_reference_image')) {
                $table->string('amazon_reference_image', 2000)->nullable()->after('amazon_reference_price');
            }
            if (!Schema::hasColumn('products', 'amazon_reference_url')) {
                $table->string('amazon_reference_url', 2000)->nullable()->after('amazon_reference_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $cols = [];
            if (Schema::hasColumn('products', 'amazon_reference_image')) $cols[] = 'amazon_reference_image';
            if (Schema::hasColumn('products', 'amazon_reference_url')) $cols[] = 'amazon_reference_url';
            if (!empty($cols)) $table->dropColumn($cols);
        });
    }
};
