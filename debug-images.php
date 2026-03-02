<?php
/**
 * Debug script to check image URLs and quality
 * Run: php debug-images.php BB0SQBXCTL
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$sku = $argv[1] ?? 'BB0SQBXCTL';

echo "=== DEBUG IMAGE QUALITY FOR SKU: $sku ===\n\n";

$product = \App\Models\Product::where('sku', $sku)->first();

if (!$product) {
    echo "❌ Product not found!\n";
    exit(1);
}

echo "Product: {$product->name}\n";
echo "SKU: {$product->sku}\n\n";

echo "--- MAIN AMAZON IMAGE ---\n";
echo "Original URL: " . ($product->amazon_reference_image ?? 'NULL') . "\n";
echo "HD URL (4K): " . ($product->amazon_reference_image_hd ?? 'NULL') . "\n";

if ($product->amazon_reference_image_hd) {
    if (strpos($product->amazon_reference_image_hd, '_SL3000_') !== false || 
        strpos($product->amazon_reference_image_hd, '_AC_SL3000_') !== false) {
        echo "✅ 4K URL detected (_SL3000_)\n";
    } else {
        echo "❌ NOT 4K! Missing _SL3000_ parameter\n";
    }
}

echo "\n--- PRODUCT IMAGES ---\n";
foreach ($product->images as $index => $image) {
    echo "\nImage #{$index}:\n";
    echo "  Path: {$image->image_path}\n";
    echo "  HD URL: " . ($image->hd_url ?? 'NULL') . "\n";
    
    if ($image->hd_url) {
        if (strpos($image->hd_url, '_SL3000_') !== false || 
            strpos($image->hd_url, '_AC_SL3000_') !== false) {
            echo "  ✅ 4K URL detected\n";
        } else if (strpos($image->hd_url, 'media-amazon.com') !== false || 
                   strpos($image->hd_url, 'images-amazon.com') !== false) {
            echo "  ❌ Amazon URL but NOT 4K!\n";
            
            // Check what size parameter it has
            if (preg_match('/_SL(\d+)_/', $image->hd_url, $matches)) {
                echo "  Current size: {$matches[1]}px (should be 3000px)\n";
            }
        } else {
            echo "  ℹ️  Local uploaded image\n";
        }
    } else {
        echo "  ⚠️  No HD URL - will use storage path\n";
    }
}

echo "\n--- IMAGE DISPLAY URLS (What the view will show) ---\n";
$imageDisplayUrls = [];
$amazonHdUrl = $product->amazon_reference_image_hd;
foreach ($product->images as $index => $image) {
    $url = $image->hd_url ?? null;
    if ($url === null && $index === 0 && $amazonHdUrl) {
        $url = $amazonHdUrl;
    }
    $finalUrl = $url ?? asset('storage/' . $image->image_path);
    $imageDisplayUrls[] = $finalUrl;
    
    echo "\nDisplay URL #{$index}:\n";
    echo "  $finalUrl\n";
    
    if (strpos($finalUrl, '_SL3000_') !== false || strpos($finalUrl, '_AC_SL3000_') !== false) {
        echo "  ✅ This will load in 4K\n";
    } else if (strpos($finalUrl, 'media-amazon.com') !== false || strpos($finalUrl, 'images-amazon.com') !== false) {
        echo "  ❌ Amazon URL but NOT 4K!\n";
    }
}

echo "\n=== RECOMMENDATION ===\n";
$needsUpgrade = false;
foreach ($product->images as $image) {
    if ($image->hd_url && 
        (strpos($image->hd_url, 'amazon.com') !== false) && 
        (strpos($image->hd_url, '_SL3000_') === false && strpos($image->hd_url, '_AC_SL3000_') === false)) {
        $needsUpgrade = true;
        break;
    }
}

if ($needsUpgrade) {
    echo "❌ Database has low-res URLs. Run this command:\n";
    echo "   php artisan images:upgrade-4k\n";
} else {
    echo "✅ URLs look correct. Check browser cache or Amazon CDN.\n";
    echo "   Try: Ctrl+Shift+R to hard refresh\n";
}

echo "\n";
