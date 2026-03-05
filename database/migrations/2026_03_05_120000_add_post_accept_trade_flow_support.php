<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Messages: support system messages (sender_id nullable, is_system flag)
        $dbName = config('database.connections.'.config('database.default').'.database');
        $fkExists = DB::selectOne("
            SELECT 1 FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = 'messages'
            AND CONSTRAINT_NAME = 'messages_sender_id_foreign' AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$dbName]);
        if ($fkExists) {
            Schema::table('messages', function (Blueprint $table) {
                $table->dropForeign(['sender_id']);
            });
        }
        Schema::table('messages', function (Blueprint $table) {
            $table->unsignedBigInteger('sender_id')->nullable()->change();
            if (! Schema::hasColumn('messages', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('conversation_id');
            }
            if (! Schema::hasColumn('messages', 'system_type')) {
                $table->string('system_type', 50)->nullable()->after('conversation_id');
            }
        });

        // Conversations: welcome message sent when offerer first opens
        if (! Schema::hasColumn('conversations', 'welcome_message_sent')) {
            Schema::table('conversations', function (Blueprint $table) {
                $table->boolean('welcome_message_sent')->default(false)->after('is_locked');
            });
        }

        // Trades: who cancelled (for "Rejected by User X" in history)
        if (! Schema::hasColumn('trades', 'cancelled_by_user_id')) {
            Schema::table('trades', function (Blueprint $table) {
                $table->foreignId('cancelled_by_user_id')->nullable()->after('participant_cancel_requested_at')->constrained('users')->nullOnDelete();
            });
        }

        // Users: offence count and suspended_until for escalation (5d, 30d, ban)
        Schema::table('users', function (Blueprint $table) {
            if (! Schema::hasColumn('users', 'trade_suspension_offence_count')) {
                $table->unsignedTinyInteger('trade_suspension_offence_count')->default(0)->after('trade_suspended_by');
            }
            if (! Schema::hasColumn('users', 'trade_suspended_until')) {
                $after = Schema::hasColumn('users', 'trade_suspension_offence_count') ? 'trade_suspension_offence_count' : 'trade_suspended_by';
                $table->timestamp('trade_suspended_until')->nullable()->after($after);
            }
        });

        // Conversation reports: who was reported (for suspension logic)
        if (! Schema::hasColumn('conversation_reports', 'reported_user_id')) {
            Schema::table('conversation_reports', function (Blueprint $table) {
                $table->foreignId('reported_user_id')->nullable()->after('reporter_id')->constrained('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['is_system', 'system_type']);
            $table->unsignedBigInteger('sender_id')->nullable(false)->change();
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
        });
        Schema::table('conversations', function (Blueprint $table) {
            $table->dropColumn('welcome_message_sent');
        });
        Schema::table('trades', function (Blueprint $table) {
            $table->dropConstrainedForeignId('cancelled_by_user_id');
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['trade_suspension_offence_count', 'trade_suspended_until']);
        });
        Schema::table('conversation_reports', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reported_user_id');
        });
    }
};
