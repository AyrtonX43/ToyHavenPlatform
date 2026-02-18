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
        Schema::create('business_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained()->onDelete('cascade');
            $table->enum('platform', ['facebook', 'instagram', 'twitter', 'youtube', 'tiktok', 'linkedin', 'pinterest', 'other'])->default('other');
            $table->string('url');
            $table->string('display_name')->nullable()->comment('Custom display name for the link');
            $table->integer('display_order')->default(0)->comment('Order for display');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['seller_id', 'platform']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_social_links');
    }
};
