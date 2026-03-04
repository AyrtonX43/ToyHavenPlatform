<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->string('auction_seller_slug', 100)->nullable()->after('auction_business_name');
        });

        $this->backfillSlugs();

        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->unique('auction_seller_slug');
        });
    }

    public function down(): void
    {
        Schema::table('auction_seller_verifications', function (Blueprint $table) {
            $table->dropUnique(['auction_seller_slug']);
            $table->dropColumn('auction_seller_slug');
        });
    }

    private function backfillSlugs(): void
    {
        $verifications = \DB::table('auction_seller_verifications')
            ->where('status', 'approved')
            ->whereNull('auction_seller_slug')
            ->get();

        $used = [];

        foreach ($verifications as $v) {
            $base = $v->auction_business_name
                ? Str::slug($v->auction_business_name)
                : 'seller-' . $v->user_id;

            if (empty($base)) {
                $base = 'seller-' . $v->user_id;
            }

            $slug = $base;
            $counter = 1;
            while (in_array($slug, $used, true)) {
                $slug = $base . '-' . $counter;
                $counter++;
            }
            $used[] = $slug;

            \DB::table('auction_seller_verifications')
                ->where('id', $v->id)
                ->update(['auction_seller_slug' => $slug]);
        }
    }
};
