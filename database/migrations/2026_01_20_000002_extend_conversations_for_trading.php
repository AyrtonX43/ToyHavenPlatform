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
        Schema::table('conversations', function (Blueprint $table) {
            if (! Schema::hasColumn('conversations', 'user1_id')) {
                $table->foreignId('user1_id')->nullable()->after('customer_id')->constrained('users')->onDelete('cascade');
            }
            if (! Schema::hasColumn('conversations', 'user2_id')) {
                $table->foreignId('user2_id')->nullable()->after('user1_id')->constrained('users')->onDelete('cascade');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropForeign(['user1_id']);
            $table->dropForeign(['user2_id']);
            $table->dropColumn(['user1_id', 'user2_id']);
        });
    }
};
