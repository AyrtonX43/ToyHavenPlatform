<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'scheduled_end_at')) {
                $table->timestamp('scheduled_end_at')->nullable()->after('duration_hours');
            }
        });

        if (! Schema::hasTable('auction_category')) {
            Schema::create('auction_category', function (Blueprint $table) {
                $table->id();
                $table->foreignId('auction_id')->constrained()->onDelete('cascade');
                $table->foreignId('category_id')->constrained()->onDelete('cascade');
                $table->unsignedTinyInteger('sort_order')->default(0);
                $table->timestamps();
                $table->unique(['auction_id', 'category_id']);
            });
        }

        if (Schema::hasTable('auction_images') && ! Schema::hasColumn('auction_images', 'sort_order')) {
            Schema::table('auction_images', function (Blueprint $table) {
                $table->unsignedInteger('sort_order')->default(0)->after('is_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            if (Schema::hasColumn('auctions', 'scheduled_end_at')) {
                $table->dropColumn('scheduled_end_at');
            }
        });
        Schema::dropIfExists('auction_category');
        if (Schema::hasTable('auction_images') && Schema::hasColumn('auction_images', 'sort_order')) {
            Schema::table('auction_images', function (Blueprint $table) {
                $table->dropColumn('sort_order');
            });
        }
    }
};
