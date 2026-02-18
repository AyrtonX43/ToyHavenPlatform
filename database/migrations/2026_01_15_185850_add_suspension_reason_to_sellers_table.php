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
        Schema::table('sellers', function (Blueprint $table) {
            $table->text('suspension_reason')->nullable()->after('is_active');
            $table->timestamp('suspended_at')->nullable()->after('suspension_reason');
            $table->foreignId('suspended_by')->nullable()->after('suspended_at')->constrained('users')->onDelete('set null');
            $table->foreignId('related_report_id')->nullable()->after('suspended_by')->constrained('reports')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropForeign(['related_report_id']);
            $table->dropColumn(['suspension_reason', 'suspended_at', 'suspended_by', 'related_report_id']);
        });
    }
};
