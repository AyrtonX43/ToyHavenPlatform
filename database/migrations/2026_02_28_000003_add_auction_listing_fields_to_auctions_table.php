<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->enum('auction_type', ['timed', 'live_event'])->default('timed')->after('status');
            $table->enum('box_condition', ['sealed', 'opened_complete', 'opened_incomplete', 'no_box'])->nullable()->after('description');
            $table->text('authenticity_marks')->nullable()->after('box_condition');
            $table->text('known_defects')->nullable()->after('authenticity_marks');
            $table->text('provenance')->nullable()->after('known_defects');
            $table->string('verification_video_path')->nullable()->after('provenance');
            $table->json('allowed_bidder_plans')->nullable()->after('early_access_hours');
            $table->boolean('is_promoted')->default(false)->after('views_count');
            $table->timestamp('promoted_until')->nullable()->after('is_promoted');
            $table->integer('duration_minutes')->nullable()->after('end_at');
        });
    }

    public function down(): void
    {
        Schema::table('auctions', function (Blueprint $table) {
            $table->dropColumn([
                'auction_type', 'box_condition', 'authenticity_marks',
                'known_defects', 'provenance', 'verification_video_path',
                'allowed_bidder_plans', 'is_promoted', 'promoted_until',
                'duration_minutes',
            ]);
        });
    }
};
