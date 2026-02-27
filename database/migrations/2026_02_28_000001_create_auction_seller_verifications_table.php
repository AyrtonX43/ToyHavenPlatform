<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_seller_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('seller_id')->nullable()->constrained()->onDelete('cascade');
            $table->enum('seller_type', ['individual', 'business']);
            $table->enum('status', ['pending', 'approved', 'rejected', 'requires_resubmission'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->string('phone')->nullable();
            $table->text('address')->nullable();
            $table->string('selfie_path')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_seller_verifications');
    }
};
