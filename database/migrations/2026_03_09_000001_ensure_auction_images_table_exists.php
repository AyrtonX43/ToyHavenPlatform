<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auction_images')) {
            Schema::create('auction_images', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auction_id')->constrained()->onDelete('cascade');
                $table->string('image_path');
                $table->boolean('is_primary')->default(false);
                $table->unsignedInteger('display_order')->default(0);
                $table->timestamps();
                $table->index('auction_id');
            });
        } elseif (! Schema::hasColumn('auction_images', 'display_order')) {
            Schema::table('auction_images', function (Blueprint $table) {
                $table->unsignedInteger('display_order')->default(0)->after('is_primary');
            });
        }
    }

    public function down(): void
    {
        // Do not drop - other migrations may depend on it
    }
};
