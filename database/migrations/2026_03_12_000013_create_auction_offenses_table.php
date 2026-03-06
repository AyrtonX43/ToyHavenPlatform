<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auction_offenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('offense_type')->default('payment_deadline_missed');
            $table->integer('occurrence')->comment('1, 2, or 3');
            $table->timestamp('suspended_until')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'offense_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auction_offenses');
    }
};
