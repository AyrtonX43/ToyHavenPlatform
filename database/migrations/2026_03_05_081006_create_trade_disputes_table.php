<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained('trades')->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['no_show', 'wrong_item', 'damaged', 'other'])->default('no_show');
            $table->text('description');
            $table->json('evidence_images')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->enum('resolution_type', ['completed', 'cancelled'])->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trade_disputes');
    }
};
