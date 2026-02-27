<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->foreignId('auction_id')->nullable()->after('reportable_id')->constrained('auctions')->onDelete('set null');
            $table->foreignId('auction_payment_id')->nullable()->after('auction_id')->constrained('auction_payments')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropForeign(['auction_id']);
            $table->dropForeign(['auction_payment_id']);
            $table->dropColumn(['auction_id', 'auction_payment_id']);
        });
    }
};
