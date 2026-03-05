<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversation_reports', function (Blueprint $table) {
            $table->json('proof_images')->nullable()->after('snapshot');
        });
    }

    public function down(): void
    {
        Schema::table('conversation_reports', function (Blueprint $table) {
            $table->dropColumn('proof_images');
        });
    }
};
