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
        Schema::create('trade_disputes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained('trades')->onDelete('cascade');
            $table->foreignId('reporter_id')->constrained('users')->onDelete('cascade');
            $table->enum('type', ['not_received', 'damaged', 'wrong_item', 'other'])->default('not_received');
            $table->text('description');
            $table->json('evidence_images')->nullable();
            $table->enum('status', ['open', 'investigating', 'resolved', 'closed'])->default('open');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->text('resolution_notes')->nullable();
            $table->enum('resolution_type', ['completed', 'cancelled'])->nullable()->comment('How the trade was resolved');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index('trade_id');
            $table->index('reporter_id');
            $table->index('status');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_disputes');
    }
};
