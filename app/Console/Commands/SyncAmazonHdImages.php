<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductImage;

class SyncAmazonHdImages extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'products:sync-amazon-hd-images 
                            {--dry-run : Show what would be updated without making changes}
                            {--force : Update all products even if they already have HD URLs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync existing products to use Amazon HD image URLs (1500px+) for better quality';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        $this->info('🔍 Scanning products with Amazon reference images...');
        $this->newLine();

        // Find products with Amazon reference images
        $products = Product::whereNotNull('amazon_reference_image')
            ->where('amazon_reference_image', '!=', '')
            ->with('images')
            ->get();

        if ($products->isEmpty()) {
            $this->warn('No products found with Amazon reference images.');
            return 0;
        }

        $this->info("Found {$products->count()} products with Amazon references.");
        $this->newLine();

        $updated = 0;
        $skipped = 0;
        $errors = 0;

        $progressBar = $this->output->createProgressBar($products->count());
        $progressBar->start();

        foreach ($products as $product) {
            try {
                // Get the HD version of the Amazon reference image
                $hdUrl = $this->convertToHdUrl($product->amazon_reference_image);
                
                if (!$hdUrl) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Check if product has images
                if ($product->images->isEmpty()) {
                    $skipped++;
                    $progressBar->advance();
                    continue;
                }

                // Update the first image's hd_url if it doesn't have one or if force is enabled
                $firstImage = $product->images->first();
                
                if ($force || empty($firstImage->hd_url)) {
                    if (!$dryRun) {
                        $firstImage->update(['hd_url' => $hdUrl]);
                    }
                    $updated++;
                } else {
                    $skipped++;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $errors++;
                $progressBar->advance();
                $this->newLine();
                $this->error("Error processing product ID {$product->id}: " . $e->getMessage());
            }
        }

        $progressBar->finish();
        $this->newLine(2);

        // Summary
        $this->info('✅ Sync completed!');
        $this->newLine();
        
        $this->table(
            ['Status', 'Count'],
            [
                ['Updated' . ($dryRun ? ' (would be)' : ''), $updated],
                ['Skipped', $skipped],
                ['Errors', $errors],
                ['Total', $products->count()],
            ]
        );

        if ($dryRun) {
            $this->newLine();
            $this->warn('🔸 DRY RUN MODE - No changes were made.');
            $this->info('Run without --dry-run to apply changes.');
        } else {
            $this->newLine();
            $this->info('🎉 All products have been synced with HD image URLs!');
            $this->info('Customers will now see high-quality Amazon images.');
        }

        return 0;
    }

    /**
     * Convert Amazon image URL to HD version (1500px+)
     */
    private function convertToHdUrl($url)
    {
        if (empty($url)) {
            return null;
        }

        // Check if it's an Amazon image
        if (strpos($url, 'media-amazon.com') === false && strpos($url, 'images-amazon.com') === false) {
            return null;
        }

        // Convert to HD size (1500px)
        $hdUrl = preg_replace('/_S[LX]\d+_/', '_AC_SL1500_', $url);
        $hdUrl = preg_replace('/_SL\d+_/', '_SL1500_', $hdUrl);
        $hdUrl = preg_replace('/_AC_SL\d+_/', '_AC_SL1500_', $hdUrl);

        return $hdUrl;
    }
}
