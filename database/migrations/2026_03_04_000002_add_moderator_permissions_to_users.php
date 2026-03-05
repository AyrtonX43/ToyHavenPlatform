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
        if (Schema::hasColumn('users', 'moderator_permissions')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->json('moderator_permissions')->nullable()->after('role');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'moderator_permissions')) {
            return;
        }
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('moderator_permissions');
        });
    }
};
