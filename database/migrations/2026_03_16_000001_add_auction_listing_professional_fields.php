<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auctions')) {
            Schema::table('auctions', function (Blueprint $table) {
                if (! Schema::hasColumn('auctions', 'condition')) {
                    $table->string('condition')->default('good')->after('description')
                        ->comment('new, like_new, good, fair');
                }
                if (! Schema::hasColumn('auctions', 'reserve_price')) {
                    $table->decimal('reserve_price', 12, 2)->nullable()->after('starting_bid');
                }
                if (! Schema::hasColumn('auctions', 'min_watchers_to_approve')) {
                    $table->unsignedInteger('min_watchers_to_approve')->nullable()->after('rejection_reason');
                }
                if (! Schema::hasColumn('auctions', 'auction_outcome')) {
                    $table->string('auction_outcome')->nullable()
                        ->comment('sold, reserve_not_met, no_bids');
                }
            });
        }

        if (Schema::hasTable('auction_payments')) {
            Schema::table('auction_payments', function (Blueprint $table) {
                if (! Schema::hasColumn('auction_payments', 'tracking_number')) {
                    $table->string('tracking_number')->nullable()->after('delivery_status');
                }
            });
        }

        if (! Schema::hasTable('saved_auctions')) {
            Schema::create('saved_auctions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('auction_id')->constrained()->onDelete('cascade');
                $table->timestamp('notified_at')->nullable();
                $table->timestamps();

                $table->unique(['user_id', 'auction_id']);
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('auctions')) {
            Schema::table('auctions', function (Blueprint $table) {
                $cols = [];
                if (Schema::hasColumn('auctions', 'condition')) {
                    $cols[] = 'condition';
                }
                if (Schema::hasColumn('auctions', 'reserve_price')) {
                    $cols[] = 'reserve_price';
                }
                if (Schema::hasColumn('auctions', 'min_watchers_to_approve')) {
                    $cols[] = 'min_watchers_to_approve';
                }
                if (Schema::hasColumn('auctions', 'auction_outcome')) {
                    $cols[] = 'auction_outcome';
                }
                if (! empty($cols)) {
                    $table->dropColumn($cols);
                }
            });
        }

        if (Schema::hasTable('auction_payments') && Schema::hasColumn('auction_payments', 'tracking_number')) {
            Schema::table('auction_payments', function (Blueprint $table) {
                $table->dropColumn('tracking_number');
            });
        }
    }
};
