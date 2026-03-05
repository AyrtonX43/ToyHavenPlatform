<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trade_proofs', function (Blueprint $table) {
            $table->json('proof_images')->nullable()->after('proof_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('trade_proofs', function (Blueprint $table) {
            $table->dropColumn('proof_images');
        });
    }
};
