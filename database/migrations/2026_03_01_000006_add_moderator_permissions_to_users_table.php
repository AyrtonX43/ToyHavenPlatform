<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('moderator_permissions')->nullable()->after('role');
            $table->timestamp('moderator_assigned_at')->nullable()->after('moderator_permissions');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null')->after('moderator_assigned_at');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['assigned_by']);
            $table->dropColumn(['moderator_permissions', 'moderator_assigned_at', 'assigned_by']);
        });
    }
};
