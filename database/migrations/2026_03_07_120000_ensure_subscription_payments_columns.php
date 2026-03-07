<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        if (! Schema::hasColumn('subscription_payments', 'payment_reference')) {
            DB::statement('ALTER TABLE subscription_payments ADD COLUMN payment_reference VARCHAR(255) NULL');
        }

        if (! Schema::hasColumn('subscription_payments', 'payment_method')) {
            DB::statement('ALTER TABLE subscription_payments ADD COLUMN payment_method VARCHAR(50) NULL');
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('subscription_payments')) {
            return;
        }

        if (Schema::hasColumn('subscription_payments', 'payment_reference')) {
            DB::statement('ALTER TABLE subscription_payments DROP COLUMN payment_reference');
        }

        if (Schema::hasColumn('subscription_payments', 'payment_method')) {
            DB::statement('ALTER TABLE subscription_payments DROP COLUMN payment_method');
        }
    }
};
