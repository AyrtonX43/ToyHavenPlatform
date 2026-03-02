<?php

namespace App\Console\Commands;

use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class Upgrade4kImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'images:upgrade-4k {--dry-run : Show what would be updated without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade existing Amazon product images to 1080P-4K HDR range (2500px)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
            $this->newLine();
        }

        $this->info('🚀 Starting 1080P-4K HDR Image Upgrade...');
        $this->newLine();

        // Update products table - amazon_reference_image field
        $this->info('📦 Upgrading product Amazon reference images...');
        $productsToUpdate = Product::whereNotNull('amazon_reference_image')
            ->where('amazon_reference_image', '!=', '')
            ->where(function($query) {
                $query->where('amazon_reference_image', 'like', '%media-amazon.com%')
                      ->orWhere('amazon_reference_image', 'like', '%images-amazon.com%');
            })
            ->get();

        $productCount = 0;
        foreach ($productsToUpdate as $product) {
            $oldUrl = $product->amazon_reference_image;
            $newUrl = $this->upgradeImageUrl($oldUrl);
            
            if ($oldUrl !== $newUrl) {
                $this->line("  Product #{$product->id}: {$product->name}");
                $this->line("    Old: {$oldUrl}");
                $this->line("    New: {$newUrl}");
                
                if (!$dryRun) {
                    $product->update(['amazon_reference_image' => $newUrl]);
                }
                $productCount++;
            }
        }
        
        $this->info("✅ Updated {$productCount} product Amazon reference images");
        $this->newLine();

        // Update product_images table - hd_url field
        $this->info('🖼️  Upgrading product image HD URLs...');
        $imagesToUpdate = ProductImage::whereNotNull('hd_url')
            ->where('hd_url', '!=', '')
            ->where(function($query) {
                $query->where('hd_url', 'like', '%media-amazon.com%')
                      ->orWhere('hd_url', 'like', '%images-amazon.com%');
            })
            ->get();

        $imageCount = 0;
        foreach ($imagesToUpdate as $image) {
            $oldUrl = $image->hd_url;
            $newUrl = $this->upgradeImageUrl($oldUrl);
            
            if ($oldUrl !== $newUrl) {
                $this->line("  Image #{$image->id} (Product #{$image->product_id})");
                $this->line("    Old: {$oldUrl}");
                $this->line("    New: {$newUrl}");
                
                if (!$dryRun) {
                    $image->update(['hd_url' => $newUrl]);
                }
                $imageCount++;
            }
        }
        
        $this->info("✅ Updated {$imageCount} product image HD URLs");
        $this->newLine();

        // Summary
        $this->info('📊 Summary:');
        $this->table(
            ['Type', 'Count'],
            [
                ['Product Amazon References', $productCount],
                ['Product Image HD URLs', $imageCount],
                ['Total Images Upgraded', $productCount + $imageCount],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('⚠️  This was a DRY RUN - no changes were made');
            $this->info('Run without --dry-run to apply changes: php artisan images:upgrade-4k');
        } else {
            $this->newLine();
            $this->info('🎉 1080P-4K HDR upgrade complete!');
            $this->info('All Amazon images are now fetched at 2500px (1080P-4K HDR range) resolution.');
        }

        return Command::SUCCESS;
    }

    /**
     * Upgrade image URL to 1080P-4K HDR range (2500px)
     */
    private function upgradeImageUrl(string $url): string
    {
        if (empty($url)) {
            return $url;
        }

        // Only process Amazon URLs
        if (strpos($url, 'media-amazon.com') === false && strpos($url, 'images-amazon.com') === false) {
            return $url;
        }

        // Upgrade to 2500px for optimal 1080P-4K HDR quality balance
        $url = preg_replace('/_S[LXY]\d+_/', '_AC_SL2500_', $url);
        $url = preg_replace('/_SL\d+_/', '_SL2500_', $url);
        $url = preg_replace('/_AC_SL\d+_/', '_AC_SL2500_', $url);
        $url = preg_replace('/_AC_SX\d+_/', '_AC_SL2500_', $url);
        $url = preg_replace('/_AC_SY\d+_/', '_AC_SL2500_', $url);
        
        // Remove any remaining size constraints
        $url = preg_replace('/\._[A-Z]{2}\d+_\./', '.', $url);

        return $url;
    }
}
