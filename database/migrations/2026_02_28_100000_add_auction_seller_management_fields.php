<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->string('auction_business_name')->nullable()->after('seller_type');
            $table->boolean('is_suspended')->default(false)->after('verified_by');
            $table->timestamp('suspended_at')->nullable()->after('is_suspended');
            $table->text('suspension_reason')->nullable()->after('suspended_at');
            $table->foreignId('suspended_by')->nullable()->constrained('users')->after('suspension_reason');
        });
    }

    public function down(): void
    {
        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->dropForeign(['suspended_by']);
            $table->dropColumn(['auction_business_name', 'is_suspended', 'suspended_at', 'suspension_reason', 'suspended_by']);
        });
    }
};
