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
        Schema::create('business_page_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->unique()->constrained()->onDelete('cascade');
            $table->string('page_name')->nullable()->comment('Custom business page display name');
            $table->text('business_description')->nullable()->comment('Rich text business description');
            $table->string('logo_path')->nullable()->comment('Business logo file path');
            $table->string('banner_path')->nullable()->comment('Banner/cover image file path');
            $table->string('primary_color')->default('#007bff')->comment('Primary brand color');
            $table->string('secondary_color')->default('#6c757d')->comment('Secondary brand color');
            $table->enum('layout_type', ['grid', 'list', 'featured'])->default('grid');
            $table->string('meta_title')->nullable()->comment('SEO meta title');
            $table->text('meta_description')->nullable()->comment('SEO meta description');
            $table->json('keywords')->nullable()->comment('SEO keywords/tags');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_page_settings');
    }
};
