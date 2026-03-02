<?php

namespace App\Http\Controllers\Seller;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProductController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:seller,admin');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $seller = Auth::user()->seller;
        
        $query = Product::where('seller_id', $seller->id)
            ->with(['categories', 'images']);
        
        // Apply filters
        if (request()->has('search') && request()->search) {
            $search = request()->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('sku', 'like', "%{$search}%");
            });
        }
        
        if (request()->has('status') && request()->status) {
            $query->where('status', request()->status);
        }
        
        if (request()->has('stock')) {
            if (request()->stock === 'low_stock') {
                $query->where('stock_quantity', '<=', 10)->where('stock_quantity', '>', 0);
            } elseif (request()->stock === 'in_stock') {
                $query->where('stock_quantity', '>', 10);
            } elseif (request()->stock === 'out_of_stock') {
                $query->where('stock_quantity', '=', 0);
            }
        }
        
        if (request()->has('category_id') && request()->category_id) {
            $query->whereHas('categories', function($q) {
                $q->where('categories.id', request()->category_id);
            });
        }
        
        $products = $query->orderBy('created_at', 'desc')->paginate(15);
        
        $categories = Category::where('is_active', true)->get();

        return view('seller.products.index', compact('products', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('seller.products.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $seller = Auth::user()->seller;

        // Get primary category (first selected category)
        // Ensure categories array exists and has at least one item
        $categories = $request->input('categories', []);
        if (empty($categories) || !is_array($categories)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categories' => 'Please select at least one category.']);
        }
        
        $primaryCategoryId = $categories[0] ?? null;
        
        if (!$primaryCategoryId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categories' => 'Please select at least one valid category.']);
        }
        
        // Get base price and final price
        $basePrice = $request->base_price ?? $request->price; // Base price (before fees/tax)
        $finalPrice = $request->final_price ?? $request->price; // Final price (with fees/tax)
        
        // If base_price is not provided but final_price is, calculate base from final
        if (!$request->base_price && $request->final_price && $request->final_price != $request->price) {
            // Reverse calculate: final = (base + base*fee) + (base + base*fee)*tax
            // final = base * (1 + fee) * (1 + tax)
            // base = final / ((1 + fee) * (1 + tax))
            $platformFeePercent = ($request->platform_fee_percentage ?? 5.00) / 100;
            $taxPercent = 12.00 / 100;
            $basePrice = $finalPrice / ((1 + $platformFeePercent) * (1 + $taxPercent));
        }
        
        $product = Product::create([
            'seller_id' => $seller->id,
            'category_id' => $primaryCategoryId, // Primary category for backward compatibility
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . time(),
            'description' => $request->description,
            'brand' => $request->brand,
            'sku' => $request->sku,
            'price' => $finalPrice, // Final price (what customers pay)
            'base_price' => $basePrice, // Base price (before fees/tax)
            'amazon_reference_price' => $request->amazon_reference_price,
            'amazon_reference_image' => $request->amazon_reference_image,
            'amazon_reference_url' => $request->amazon_reference_url,
            'platform_fee_percentage' => $request->platform_fee_percentage ?? 5.00,
            'tax_percentage' => 12.00, // Philippine VAT
            'final_price' => $finalPrice,
            'stock_quantity' => $request->stock_quantity,
            'status' => 'pending', // Admin approval required - will appear in toyshop once approved
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'video_url' => $request->filled('video_url') ? trim($request->video_url) : null,
        ]);
        
        // Attach multiple categories
        if (!empty($categories) && is_array($categories)) {
            $product->categories()->attach($categories);
        }
        
        // Save product options (variants)
        if ($request->filled('variations_json')) {
            $variations = json_decode($request->variations_json, true);
            if (is_array($variations)) {
                foreach ($variations as $index => $variation) {
                    \App\Models\ProductVariation::create([
                        'product_id' => $product->id,
                        'variation_type' => $variation['variation_type'] ?? 'Variant',
                        'variation_value' => $variation['variation_value'] ?? '',
                        'price_adjustment' => $variation['price_adjustment'] ?? 0,
                        'stock_quantity' => $variation['stock_quantity'] ?? 0,
                        'is_available' => $variation['is_available'] ?? true,
                        'display_order' => $index,
                    ]);
                }
            }
        }
        
        $imageIndex = 0;
        
        // Upload images from file inputs
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $product->id, 'public');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => $imageIndex === 0,
                    'display_order' => $imageIndex++,
                ]);
            }
        }
        
        // Download and save imported images from URLs
        if ($request->filled('imported_image_urls')) {
            $imageUrls = array_filter(explode(',', $request->imported_image_urls));
            
            Log::info('Processing imported images', [
                'product_id' => $product->id,
                'image_count' => count($imageUrls),
                'urls' => $imageUrls,
            ]);
            
            foreach ($imageUrls as $imageUrl) {
                $imageUrl = trim($imageUrl);
                if (empty($imageUrl)) continue;
                
                try {
                    // Download image from URL (disable SSL verification for compatibility)
                    $response = Http::timeout(30)->withOptions(['verify' => false])->get($imageUrl);
                    
                    if ($response->successful()) {
                        // Get file extension from URL or content type
                        $extension = $this->getImageExtension($imageUrl, $response->header('Content-Type'));
                        $filename = 'imported_' . time() . '_' . $imageIndex . '.' . $extension;
                        $path = 'products/' . $product->id . '/' . $filename;
                        
                        // Save the image
                        Storage::disk('public')->put($path, $response->body());
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path,
                            'hd_url' => $imageUrl,
                            'is_primary' => ($imageIndex === 0 && !$product->images()->exists()),
                            'display_order' => $imageIndex++,
                        ]);
                        
                        Log::info('Image imported successfully', [
                            'product_id' => $product->id,
                            'url' => $imageUrl,
                            'path' => $path,
                        ]);
                    } else {
                        Log::warning('Failed to download image from URL: ' . $imageUrl, [
                            'status' => $response->status(),
                            'product_id' => $product->id,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to import image from URL: ' . $imageUrl, [
                        'error' => $e->getMessage(),
                        'product_id' => $product->id,
                        'trace' => $e->getTraceAsString(),
                    ]);
                    // Continue with other images even if one fails
                }
            }
        } else {
            Log::info('No imported_image_urls in request', [
                'product_id' => $product->id,
                'request_keys' => array_keys($request->all()),
            ]);
        }
        
        // Log summary of images saved
        $totalImages = $product->images()->count();
        Log::info('Product images summary', [
            'product_id' => $product->id,
            'total_images' => $totalImages,
            'has_file_images' => $request->hasFile('images'),
            'has_imported_urls' => $request->filled('imported_image_urls'),
            'imported_urls_count' => $request->filled('imported_image_urls') ? count(array_filter(explode(',', $request->imported_image_urls))) : 0,
        ]);
        
        // Handle video file upload
        if ($request->hasFile('video_file')) {
            try {
                $videoFile = $request->file('video_file');
                $videoPath = $videoFile->store('products/' . $product->id . '/videos', 'public');
                
                // Update product with video file path
                $product->update([
                    'video_url' => Storage::url($videoPath) // Store as URL path
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to upload video file', [
                    'error' => $e->getMessage(),
                    'product_id' => $product->id,
                ]);
                // Continue even if video upload fails
            }
        }
        
        // Download and save imported videos from URLs (from Amazon reference)
        if ($request->filled('imported_video_urls')) {
            $videoUrls = array_filter(explode(',', $request->imported_video_urls));
            
            Log::info('Processing imported videos in store method', [
                'product_id' => $product->id,
                'video_count' => count($videoUrls),
                'urls' => $videoUrls,
            ]);
            
            // Store the first video URL in video_url field (prioritize imported videos from Amazon reference)
            if (!empty($videoUrls)) {
                $firstVideoUrl = trim($videoUrls[0]);
                
                // Check if it's a YouTube or Vimeo URL (keep as URL)
                $isYouTube = str_contains($firstVideoUrl, 'youtube.com') || str_contains($firstVideoUrl, 'youtu.be');
                $isVimeo = str_contains($firstVideoUrl, 'vimeo.com');
                
                if ($isYouTube || $isVimeo) {
                    // For YouTube/Vimeo, store the URL directly
                    $product->update([
                        'video_url' => $firstVideoUrl
                    ]);
                    Log::info('Video URL saved (YouTube/Vimeo) in store', [
                        'product_id' => $product->id,
                        'video_url' => $firstVideoUrl,
                    ]);
                } else {
                    // For direct video URLs, try to download and store locally
                    try {
                        $response = Http::timeout(60)->withOptions(['verify' => false])->get($firstVideoUrl);
                        
                        if ($response->successful()) {
                            // Determine file extension from URL or content type
                            $extension = 'mp4'; // Default
                            $contentType = $response->header('Content-Type');
                            if (str_contains($contentType, 'video/')) {
                                $extension = str_replace('video/', '', explode(';', $contentType)[0]);
                            } else {
                                // Try to get from URL
                                $pathInfo = pathinfo(parse_url($firstVideoUrl, PHP_URL_PATH));
                                if (isset($pathInfo['extension'])) {
                                    $extension = $pathInfo['extension'];
                                }
                            }
                            
                            $filename = 'imported_video_' . time() . '.' . $extension;
                            $path = 'products/' . $product->id . '/videos/' . $filename;
                            
                            // Save the video
                            Storage::disk('public')->put($path, $response->body());
                            
                            $product->update([
                                'video_url' => Storage::url($path)
                            ]);
                            
                            Log::info('Video downloaded and saved successfully in store', [
                                'product_id' => $product->id,
                                'video_url' => $firstVideoUrl,
                                'saved_path' => $path,
                            ]);
                        } else {
                            // If download fails, store URL as fallback
                            $product->update([
                                'video_url' => $firstVideoUrl
                            ]);
                            Log::warning('Video download failed in store, storing URL as fallback', [
                                'product_id' => $product->id,
                                'video_url' => $firstVideoUrl,
                                'status' => $response->status(),
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to import video from URL in store: ' . $firstVideoUrl, [
                            'error' => $e->getMessage(),
                            'product_id' => $product->id,
                            'trace' => $e->getTraceAsString(),
                        ]);
                        // Store URL as fallback even if download fails
                        $product->update([
                            'video_url' => $firstVideoUrl
                        ]);
                    }
                }
            }
        } else {
            Log::info('No imported_video_urls in request (store method)', [
                'product_id' => $product->id,
                'has_video_file' => $request->hasFile('video_file'),
                'has_video_url' => $request->filled('video_url'),
            ]);
        }
        
        // Log final product status
        Log::info('Product created successfully', [
            'product_id' => $product->id,
            'total_images' => $product->images()->count(),
            'video_url' => $product->video_url,
            'base_price' => $product->base_price,
            'final_price' => $product->final_price,
            'amazon_reference_price' => $product->amazon_reference_price,
        ]);

        return redirect()->route('seller.products.index')
            ->with('success', 'Product created successfully! It will be reviewed by admin before going live. Once approved, it will appear in the Toyshop section.');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $seller = Auth::user()->seller;
        $product = Product::where('seller_id', $seller->id)
            ->with(['categories', 'images', 'reviews.user', 'variations'])
            ->findOrFail($id);

        return view('seller.products.show', compact('product'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $seller = Auth::user()->seller;
        $product = Product::where('seller_id', $seller->id)
            ->with(['images', 'categories', 'variations'])
            ->findOrFail($id);
        
        $categories = Category::where('is_active', true)->get();
        
        // Get selected category IDs for the form
        $selectedCategoryIds = $product->categories->pluck('id')->toArray();

        return view('seller.products.edit', compact('product', 'categories', 'selectedCategoryIds'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreProductRequest $request, $id)
    {
        $seller = Auth::user()->seller;
        $product = Product::where('seller_id', $seller->id)->findOrFail($id);

        // Get primary category (first selected category)
        $categories = $request->input('categories', []);
        if (empty($categories) || !is_array($categories)) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categories' => 'Please select at least one category.']);
        }
        
        $primaryCategoryId = $categories[0] ?? null;
        
        if (!$primaryCategoryId) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['categories' => 'Please select at least one valid category.']);
        }
        
        // Get base price and final price
        $basePrice = $request->base_price ?? $request->price; // Base price (before fees/tax)
        $finalPrice = $request->final_price ?? $request->price; // Final price (with fees/tax)
        
        // If base_price is not provided but final_price is, calculate base from final
        if (!$request->base_price && $request->final_price && $request->final_price != $request->price) {
            // Reverse calculate: final = (base + base*fee) + (base + base*fee)*tax
            // final = base * (1 + fee) * (1 + tax)
            // base = final / ((1 + fee) * (1 + tax))
            $platformFeePercent = ($request->platform_fee_percentage ?? 5.00) / 100;
            $taxPercent = 12.00 / 100;
            $basePrice = $finalPrice / ((1 + $platformFeePercent) * (1 + $taxPercent));
        }
        
        // Update product
        $product->update([
            'category_id' => $primaryCategoryId,
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . $product->id,
            'description' => $request->description,
            'brand' => $request->brand,
            'price' => $finalPrice, // Final price (what customers pay)
            'base_price' => $basePrice, // Base price (before fees/tax)
            'amazon_reference_price' => $request->amazon_reference_price,
            'amazon_reference_image' => $request->amazon_reference_image,
            'amazon_reference_url' => $request->amazon_reference_url,
            'platform_fee_percentage' => $request->platform_fee_percentage ?? 5.00,
            'tax_percentage' => 12.00,
            'final_price' => $finalPrice,
            'stock_quantity' => $request->stock_quantity,
            'weight' => $request->weight,
            'length' => $request->length,
            'width' => $request->width,
            'height' => $request->height,
            'video_url' => $request->input('video_url') ? trim($request->input('video_url')) : null,
        ]);
        
        // Sync categories (replace existing with new selection)
        if (!empty($categories) && is_array($categories)) {
            $product->categories()->sync($categories);
        }
        
        // Update product options (variants) — replace with submitted list
        $product->variations()->delete();
        if ($request->filled('variations_json')) {
            $variations = json_decode($request->variations_json, true);
            if (is_array($variations)) {
                foreach ($variations as $index => $variation) {
                    \App\Models\ProductVariation::create([
                        'product_id' => $product->id,
                        'variation_type' => $variation['variation_type'] ?? 'Variant',
                        'variation_value' => $variation['variation_value'] ?? '',
                        'price_adjustment' => $variation['price_adjustment'] ?? 0,
                        'stock_quantity' => $variation['stock_quantity'] ?? 0,
                        'is_available' => $variation['is_available'] ?? true,
                        'display_order' => $index,
                    ]);
                }
            }
        }
        
        // Handle removal of existing images (seller can edit images when pending or approved)
        $imagesToDelete = $request->input('images_to_delete', []);
        if (!empty($imagesToDelete) && is_array($imagesToDelete)) {
            $currentCount = $product->images()->count();
            $remainingAfterDelete = $currentCount - count($imagesToDelete);
            $newUploads = $request->hasFile('images') ? count($request->file('images')) : 0;
            $newImported = $request->filled('imported_image_urls') ? count(array_filter(explode(',', $request->imported_image_urls))) : 0;
            if ($remainingAfterDelete + $newUploads + $newImported >= 1) {
                foreach ($imagesToDelete as $imageId) {
                    $image = ProductImage::where('product_id', $product->id)->find($imageId);
                    if ($image) {
                        Storage::disk('public')->delete($image->image_path);
                        $image->delete();
                    }
                }
            }
        }

        // Handle new image uploads
        $imageIndex = $product->images()->count();
        
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products/' . $product->id, 'public');
                
                ProductImage::create([
                    'product_id' => $product->id,
                    'image_path' => $path,
                    'is_primary' => $imageIndex === 0 && $product->images->count() === 0,
                    'display_order' => $imageIndex++,
                ]);
            }
        }
        
        // Download and save imported images from URLs
        if ($request->filled('imported_image_urls')) {
            $imageUrls = array_filter(explode(',', $request->imported_image_urls));
            
            foreach ($imageUrls as $imageUrl) {
                $imageUrl = trim($imageUrl);
                if (empty($imageUrl)) continue;
                
                try {
                    $response = Http::timeout(30)->withOptions(['verify' => false])->get($imageUrl);
                    
                    if ($response->successful()) {
                        $extension = $this->getImageExtension($imageUrl, $response->header('Content-Type'));
                        $filename = 'imported_' . time() . '_' . $imageIndex . '.' . $extension;
                        $path = 'products/' . $product->id . '/' . $filename;
                        
                        Storage::disk('public')->put($path, $response->body());
                        
                        ProductImage::create([
                            'product_id' => $product->id,
                            'image_path' => $path,
                            'hd_url' => $imageUrl,
                            'is_primary' => $imageIndex === 0 && $product->images->count() === 0,
                            'display_order' => $imageIndex++,
                        ]);
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to import image from URL: ' . $imageUrl, [
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }
        
        // Handle video file upload
        if ($request->hasFile('video_file')) {
            try {
                $videoFile = $request->file('video_file');
                $videoPath = $videoFile->store('products/' . $product->id . '/videos', 'public');
                
                $product->update([
                    'video_url' => Storage::url($videoPath)
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to upload video file', [
                    'error' => $e->getMessage(),
                    'product_id' => $product->id,
                ]);
            }
        }
        
        // Download and save imported videos from URLs (from Amazon reference)
        if ($request->filled('imported_video_urls')) {
            $videoUrls = array_filter(explode(',', $request->imported_video_urls));
            
            // Store the first video URL in video_url field (prioritize imported videos from Amazon reference)
            if (!empty($videoUrls)) {
                $firstVideoUrl = trim($videoUrls[0]);
                
                // Check if it's a YouTube or Vimeo URL (keep as URL)
                $isYouTube = str_contains($firstVideoUrl, 'youtube.com') || str_contains($firstVideoUrl, 'youtu.be');
                $isVimeo = str_contains($firstVideoUrl, 'vimeo.com');
                
                if ($isYouTube || $isVimeo) {
                    // For YouTube/Vimeo, store the URL directly
                    $product->update([
                        'video_url' => $firstVideoUrl
                    ]);
                } else {
                    // For direct video URLs, try to download and store locally
                    try {
                        $response = Http::timeout(60)->withOptions(['verify' => false])->get($firstVideoUrl);
                        
                        if ($response->successful()) {
                            // Determine file extension from URL or content type
                            $extension = 'mp4'; // Default
                            $contentType = $response->header('Content-Type');
                            if (str_contains($contentType, 'video/')) {
                                $extension = str_replace('video/', '', explode(';', $contentType)[0]);
                            } else {
                                // Try to get from URL
                                $pathInfo = pathinfo(parse_url($firstVideoUrl, PHP_URL_PATH));
                                if (isset($pathInfo['extension'])) {
                                    $extension = $pathInfo['extension'];
                                }
                            }
                            
                            $filename = 'imported_video_' . time() . '.' . $extension;
                            $path = 'products/' . $product->id . '/videos/' . $filename;
                            
                            // Save the video
                            Storage::disk('public')->put($path, $response->body());
                            
                            $product->update([
                                'video_url' => Storage::url($path)
                            ]);
                        } else {
                            // If download fails, store URL as fallback
                            $product->update([
                                'video_url' => $firstVideoUrl
                            ]);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to import video from URL: ' . $firstVideoUrl, [
                            'error' => $e->getMessage(),
                            'product_id' => $product->id,
                        ]);
                        // Store URL as fallback even if download fails
                        $product->update([
                            'video_url' => $firstVideoUrl
                        ]);
                    }
                }
            }
        }
        
        // Log video status
        if ($product->video_url) {
            Log::info('Product video updated successfully', [
                'product_id' => $product->id,
                'video_url' => $product->video_url,
            ]);
        }

        return redirect()->route('seller.products.index')
            ->with('success', 'Product updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $seller = Auth::user()->seller;
        $product = Product::where('seller_id', $seller->id)->findOrFail($id);

        // Delete images
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image_path);
            $image->delete();
        }

        $product->delete();

        return redirect()->route('seller.products.index')
            ->with('success', 'Product deleted successfully!');
    }
    
    /**
     * Get image extension from URL or content type
     */
    private function getImageExtension($url, $contentType = null)
    {
        // Try to get extension from URL
        $urlPath = parse_url($url, PHP_URL_PATH);
        if ($urlPath) {
            $extension = pathinfo($urlPath, PATHINFO_EXTENSION);
            if (in_array(strtolower($extension), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                return $extension === 'jpg' ? 'jpeg' : $extension;
            }
        }
        
        // Try to get from content type
        if ($contentType) {
            $mimeToExt = [
                'image/jpeg' => 'jpeg',
                'image/jpg' => 'jpeg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
            ];
            
            if (isset($mimeToExt[$contentType])) {
                return $mimeToExt[$contentType];
            }
        }
        
        // Default to jpeg
        return 'jpeg';
    }
}
