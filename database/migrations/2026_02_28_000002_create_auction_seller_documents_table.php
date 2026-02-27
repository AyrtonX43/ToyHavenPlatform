<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_seller_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained('auction_seller_verifications')->onDelete('cascade');
            $table->string('document_type');
            $table->string('document_path');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->timestamps();

            $table->index(['verification_id', 'document_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_seller_documents');
    }
};
