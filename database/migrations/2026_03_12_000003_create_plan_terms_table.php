<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained()->onDelete('cascade');
            $table->text('content');
            $table->string('version')->default('1.0');
            $table->timestamp('effective_at')->nullable();
            $table->timestamps();

            $table->index(['plan_id', 'effective_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_terms');
    }
};
