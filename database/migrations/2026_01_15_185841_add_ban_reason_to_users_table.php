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
        Schema::table('users', function (Blueprint $table) {
            $table->text('ban_reason')->nullable()->after('banned_at');
            $table->foreignId('banned_by')->nullable()->after('ban_reason')->constrained('users')->onDelete('set null');
            $table->foreignId('related_report_id')->nullable()->after('banned_by')->constrained('reports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['banned_by']);
            $table->dropForeign(['related_report_id']);
            $table->dropColumn(['ban_reason', 'banned_by', 'related_report_id']);
        });
    }
};
