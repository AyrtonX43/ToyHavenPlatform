<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->string('gcash_qr_code')->nullable()->after('postal_code')->comment('Path to GCash QR code image');
            $table->string('paymaya_qr_code')->nullable()->after('gcash_qr_code')->comment('Path to PayMaya QR code image');
        });
    }

    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['gcash_qr_code', 'paymaya_qr_code']);
        });
    }
};
