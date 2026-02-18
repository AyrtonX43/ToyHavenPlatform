<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->string('hd_url', 1024)->nullable()->after('image_path');
        });

        // Backfill HD URL for existing products: set first image's hd_url from product's amazon_reference_image (HD version)
        $products = DB::table('products')
            ->whereNotNull('amazon_reference_image')
            ->where('amazon_reference_image', '!=', '')
            ->pluck('amazon_reference_image', 'id');
        foreach ($products as $productId => $url) {
            $hdUrl = $this->toHdUrl((string) $url);
            if ($hdUrl === $url) {
                continue;
            }
            $firstImage = DB::table('product_images')
                ->where('product_id', $productId)
                ->orderBy('display_order')
                ->orderBy('id')
                ->first();
            if ($firstImage) {
                DB::table('product_images')->where('id', $firstImage->id)->update(['hd_url' => $hdUrl]);
            }
        }
    }

    private function toHdUrl(string $url): string
    {
        if (strpos($url, 'media-amazon.com') === false && strpos($url, 'images-amazon.com') === false) {
            return $url;
        }
        $url = preg_replace('/_S[LX]\d+_/', '_AC_SL1500_', $url);
        $url = preg_replace('/_SL\d+_/', '_SL1500_', $url);
        $url = preg_replace('/_AC_SL\d+_/', '_AC_SL1500_', $url);
        return $url;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropColumn('hd_url');
        });
    }
};
