<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_images', function (Blueprint $table) {
            $table->enum('image_type', ['standard', 'photo_360'])->default('standard')->after('path');
        });
    }

    public function down(): void
    {
        Schema::table('auction_images', function (Blueprint $table) {
            $table->dropColumn('image_type');
        });
    }
};
