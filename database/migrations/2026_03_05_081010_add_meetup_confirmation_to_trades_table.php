<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->timestamp('initiator_locked_at')->nullable()->after('completed_at');
            $table->timestamp('participant_locked_at')->nullable()->after('initiator_locked_at');
            $table->timestamp('initiator_confirmed_meetup_at')->nullable()->after('participant_locked_at');
            $table->timestamp('participant_confirmed_meetup_at')->nullable()->after('initiator_confirmed_meetup_at');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn([
                'initiator_locked_at',
                'participant_locked_at',
                'initiator_confirmed_meetup_at',
                'participant_confirmed_meetup_at',
            ]);
        });
    }
};
