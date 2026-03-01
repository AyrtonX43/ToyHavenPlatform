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
            if (!Schema::hasColumn('sellers', 'seller_type')) {
                $table->enum('seller_type', ['individual', 'business'])->default('individual')->after('user_id');
            }
            if (!Schema::hasColumn('sellers', 'selfie_path')) {
                $table->string('selfie_path')->nullable()->after('logo');
            }
        });

        Schema::table('seller_documents', function (Blueprint $table) {
            $table->string('document_type')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sellers', function (Blueprint $table) {
            $table->dropColumn(['seller_type', 'selfie_path']);
        });
    }
};
