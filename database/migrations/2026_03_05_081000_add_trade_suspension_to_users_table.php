<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('trade_suspended')->default(false);
            $table->timestamp('trade_suspended_at')->nullable();
            $table->text('trade_suspension_reason')->nullable();
            $table->foreignId('trade_suspended_by')->nullable()->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('trade_suspended_by');
            $table->dropColumn(['trade_suspended', 'trade_suspended_at', 'trade_suspension_reason']);
        });
    }
};
