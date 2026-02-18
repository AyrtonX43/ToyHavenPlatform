<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_listing_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_listing_id')->constrained('trade_listings')->onDelete('cascade');
            $table->string('image_path');
            $table->unsignedSmallInteger('display_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_listing_images');
    }
};
