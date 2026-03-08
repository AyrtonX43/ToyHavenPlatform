<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ensure auction seller tables exist (repair migration for servers where
     * migrations were marked run but tables are missing, e.g. after DB restore).
     */
    public function up(): void
    {
        if (! Schema::hasTable('auction_seller_verifications')) {
            Schema::create('auction_seller_verifications', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('set null');
                $table->string('type')->comment('individual, business');
                $table->string('verification_status')->default('pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['user_id', 'verification_status']);
            });
        }

        if (! Schema::hasColumn('auction_seller_verifications', 'business_info')) {
            Schema::table('auction_seller_verifications', function (Blueprint $table) {
                $table->json('business_info')->nullable()->after('type');
            });
        }

        if (! Schema::hasTable('auction_seller_documents')) {
            Schema::create('auction_seller_documents', function (Blueprint $table) {
                $table->id();
                $table->foreignId('verification_id')->constrained('auction_seller_verifications')->onDelete('cascade');
                $table->string('document_type');
                $table->string('document_path');
                $table->timestamps();

                $table->index(['verification_id', 'document_type']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_seller_documents');
        Schema::dropIfExists('auction_seller_verifications');
    }
};
