<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->timestamp('initiator_locked_at')->nullable()->after('status');
            $table->timestamp('participant_locked_at')->nullable()->after('initiator_locked_at');
            $table->string('initiator_received_proof_path')->nullable()->after('initiator_received_at');
            $table->string('participant_received_proof_path')->nullable()->after('participant_received_at');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn([
                'initiator_locked_at',
                'participant_locked_at',
                'initiator_received_proof_path',
                'participant_received_proof_path',
            ]);
        });
    }
};
