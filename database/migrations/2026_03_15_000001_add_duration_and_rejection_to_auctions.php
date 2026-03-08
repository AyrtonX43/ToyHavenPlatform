<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('auctions')) {
            Schema::create('auctions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->foreignId('seller_id')->nullable()->constrained('sellers')->onDelete('set null');
                $table->string('seller_type')->nullable()->comment('individual, business');
                $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
                $table->foreignId('user_product_id')->nullable()->constrained('user_products')->onDelete('set null');
                $table->foreignId('category_id')->nullable()->constrained('categories')->onDelete('set null');
                $table->string('title');
                $table->text('description')->nullable();
                $table->decimal('starting_bid', 12, 2);
                $table->decimal('bid_increment', 12, 2)->default(10);
                $table->unsignedInteger('duration_hours')->nullable();
                $table->timestamp('start_at')->nullable();
                $table->timestamp('end_at')->nullable();
                $table->string('status')->default('draft')->comment('draft, pending_approval, active, ended, cancelled');
                $table->foreignId('winner_id')->nullable()->constrained('users')->onDelete('set null');
                $table->decimal('winning_amount', 12, 2)->nullable();
                $table->integer('bids_count')->default(0);
                $table->timestamp('terms_accepted_at')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->index(['status', 'end_at']);
                $table->index('start_at');
            });

            return;
        }

        Schema::table('auctions', function (Blueprint $table) {
            if (! Schema::hasColumn('auctions', 'duration_hours')) {
                $table->unsignedInteger('duration_hours')->nullable()->after('bid_increment');
            }
            if (! Schema::hasColumn('auctions', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable()->after('terms_accepted_at');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('auctions')) {
            return;
        }

        Schema::table('auctions', function (Blueprint $table) {
            $columns = [];
            if (Schema::hasColumn('auctions', 'duration_hours')) {
                $columns[] = 'duration_hours';
            }
            if (Schema::hasColumn('auctions', 'rejection_reason')) {
                $columns[] = 'rejection_reason';
            }
            if (! empty($columns)) {
                $table->dropColumn($columns);
            }
        });
    }
};
