<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('auction_bids') && ! Schema::hasColumn('auction_bids', 'bidder_alias')) {
            Schema::table('auction_bids', function (Blueprint $table) {
                $table->string('bidder_alias')->nullable()->after('user_id');
            });
        }

        if (Schema::hasTable('auctions')) {
            Schema::table('auctions', function (Blueprint $table) {
                if (! Schema::hasColumn('auctions', 'category_ids')) {
                    $table->json('category_ids')->nullable()->after('category_id');
                }
                if (! Schema::hasColumn('auctions', 'condition')) {
                    $table->string('condition')->default('good')->after('description');
                }
                if (! Schema::hasColumn('auctions', 'reserve_price')) {
                    $table->decimal('reserve_price', 12, 2)->nullable()->after('starting_bid');
                }
                if (! Schema::hasColumn('auctions', 'duration_hours')) {
                    $table->unsignedInteger('duration_hours')->nullable()->after('bid_increment');
                }
                if (! Schema::hasColumn('auctions', 'rejection_reason')) {
                    $table->text('rejection_reason')->nullable()->after('terms_accepted_at');
                }
                if (! Schema::hasColumn('auctions', 'min_watchers_to_approve')) {
                    $table->unsignedInteger('min_watchers_to_approve')->nullable();
                }
                if (! Schema::hasColumn('auctions', 'auction_outcome')) {
                    $table->string('auction_outcome')->nullable();
                }
            });
        }

        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (! Schema::hasColumn('users', 'auction_suspended_until')) {
                    $table->timestamp('auction_suspended_until')->nullable();
                }
                if (! Schema::hasColumn('users', 'auction_banned_at')) {
                    $table->timestamp('auction_banned_at')->nullable();
                }
            });
        }

        if (Schema::hasTable('auction_payments')) {
            if (! Schema::hasColumn('auction_payments', 'tracking_number')) {
                Schema::table('auction_payments', function (Blueprint $table) {
                    $table->string('tracking_number')->nullable()->after('delivery_status');
                });
            }
            if (! Schema::hasColumn('auction_payments', 'shipped_at')) {
                Schema::table('auction_payments', function (Blueprint $table) {
                    $table->timestamp('shipped_at')->nullable();
                });
            }
        }

        if (Schema::hasTable('auction_reviews') && ! Schema::hasColumn('auction_reviews', 'photos')) {
            Schema::table('auction_reviews', function (Blueprint $table) {
                $table->json('photos')->nullable()->after('feedback');
            });
        }
    }

    public function down(): void {}
};
