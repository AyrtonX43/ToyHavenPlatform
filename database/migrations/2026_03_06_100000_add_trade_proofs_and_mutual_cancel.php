<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trade_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('proof_image_path');
            $table->timestamp('submitted_at');
            $table->timestamps();

            $table->unique(['trade_id', 'user_id']);
        });

        Schema::table('trades', function (Blueprint $table) {
            $table->timestamp('initiator_cancel_requested_at')->nullable()->after('participant_confirmed_meetup_at');
            $table->timestamp('participant_cancel_requested_at')->nullable()->after('initiator_cancel_requested_at');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn(['initiator_cancel_requested_at', 'participant_cancel_requested_at']);
        });
        Schema::dropIfExists('trade_proofs');
    }
};
