<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\ProductView;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Product::with(['seller', 'category', 'images'])
            ->withCount('reviews')
            ->where('status', 'active')
            ->whereHas('seller', function($q) {
                $q->where('is_active', true)
                  ->where('verification_status', 'approved');
            });

        // Search
        if ($request->has('search') && $request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%')
                  ->orWhere('brand', 'like', '%' . $request->search . '%');
            });
        }

        // Category filter
        if ($request->has('category') && $request->category) {
            $query->where('category_id', $request->category);
        }

        // Price range filter
        if ($request->has('min_price') && $request->min_price) {
            $query->where('price', '>=', $request->min_price);
        }
        if ($request->has('max_price') && $request->max_price) {
            $query->where('price', '<=', $request->max_price);
        }

        // Seller rating filter
        if ($request->has('min_rating') && $request->min_rating) {
            $query->whereHas('seller', function($q) use ($request) {
                $q->where('rating', '>=', $request->min_rating);
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'price_low':
                $query->orderBy('price', 'asc');
                break;
            case 'price_high':
                $query->orderBy('price', 'desc');
                break;
            case 'rating':
                $query->orderBy('rating', 'desc');
                break;
            case 'popular':
                $query->orderBy('sales_count', 'desc');
                break;
            default:
                $query->orderBy('created_at', 'desc');
        }

        $products = $query->paginate(12);
        $categories = Category::where('is_active', true)->get();

        // Recommended products based on user's preferred toy categories (when logged in)
        $recommendedProducts = collect();
        if (Auth::check() && Auth::user()->hasSelectedCategories()) {
            $preferredCategoryIds = Auth::user()->categoryPreferences()->pluck('categories.id')->toArray();
            if (!empty($preferredCategoryIds)) {
                $recommendedProducts = Product::with(['seller', 'category', 'categories', 'images'])
                    ->withCount('reviews')
                    ->where('status', 'active')
                    ->whereHas('seller', function ($q) {
                        $q->where('is_active', true)->where('verification_status', 'approved');
                    })
                    ->where(function ($q) use ($preferredCategoryIds) {
                        $q->whereIn('category_id', $preferredCategoryIds)
                            ->orWhereHas('categories', function ($q2) use ($preferredCategoryIds) {
                                $q2->whereIn('categories.id', $preferredCategoryIds);
                            });
                    })
                    ->orderBy('rating', 'desc')
                    ->orderBy('sales_count', 'desc')
                    ->limit(8)
                    ->get();
            }
        }
        
        // Get wishlist data for authenticated users
        $wishlistProductIds = [];
        $wishlistItems = [];
        if (Auth::check()) {
            $wishlists = Wishlist::where('user_id', Auth::id())
                ->get();
            $wishlistProductIds = $wishlists->pluck('product_id')->toArray();
            $wishlistItems = $wishlists->keyBy('product_id')->toArray();
        }

        return view('toyshop.products.index', compact('products', 'categories', 'recommendedProducts', 'wishlistProductIds', 'wishlistItems'));
    }

    /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $product = Product::with(['seller', 'category', 'categories', 'images', 'reviews.user', 'variations'])
            ->withCount('reviews')
            ->where('slug', $slug)
            ->where('status', 'active')
            ->whereHas('seller', function($q) {
                $q->where('is_active', true)
                  ->where('verification_status', 'approved');
            })
            ->firstOrFail();

        // Track view
        if (auth()->check()) {
            ProductView::create([
                'user_id' => auth()->id(),
                'product_id' => $product->id,
                'viewed_at' => now(),
            ]);
        } else {
            ProductView::create([
                'session_id' => session()->getId(),
                'product_id' => $product->id,
                'viewed_at' => now(),
            ]);
        }

        // Increment views count
        $product->increment('views_count');

        // Get related products - use categories if available, fallback to primary category
        $categoryIds = $product->categories->pluck('id')->toArray();
        if (empty($categoryIds) && $product->category_id) {
            $categoryIds = [$product->category_id];
        }
        
        $relatedProducts = Product::where(function($query) use ($categoryIds, $product) {
                if (!empty($categoryIds)) {
                    $query->whereHas('categories', function($q) use ($categoryIds) {
                        $q->whereIn('categories.id', $categoryIds);
                    })->orWhereIn('category_id', $categoryIds);
                } else {
                    $query->where('category_id', $product->category_id);
                }
            })
            ->where('id', '!=', $product->id)
            ->where('status', 'active')
            ->whereHas('seller', function($q) {
                $q->where('is_active', true)
                  ->where('verification_status', 'approved');
            })
            ->with(['images', 'categories'])
            ->withCount('reviews')
            ->limit(4)
            ->get();

        // Recommended for you (based on user's preferred categories), when logged in
        $recommendedByPreferences = collect();
        if (Auth::check() && Auth::user()->hasSelectedCategories()) {
            $preferredCategoryIds = Auth::user()->categoryPreferences()->pluck('categories.id')->toArray();
            if (!empty($preferredCategoryIds)) {
                $recommendedByPreferences = Product::with(['images', 'categories'])
                    ->withCount('reviews')
                    ->where('status', 'active')
                    ->where('id', '!=', $product->id)
                    ->whereHas('seller', function ($q) {
                        $q->where('is_active', true)->where('verification_status', 'approved');
                    })
                    ->where(function ($q) use ($preferredCategoryIds) {
                        $q->whereIn('category_id', $preferredCategoryIds)
                            ->orWhereHas('categories', function ($q2) use ($preferredCategoryIds) {
                                $q2->whereIn('categories.id', $preferredCategoryIds);
                            });
                    })
                    ->orderBy('rating', 'desc')
                    ->limit(4)
                    ->get();
            }
        }

        // Check if product is in user's wishlist
        $inWishlist = false;
        $wishlistItem = null;
        if (Auth::check()) {
            $wishlistItem = Wishlist::where('user_id', Auth::id())
                ->where('product_id', $product->id)
                ->first();
            $inWishlist = $wishlistItem !== null;
        }

        // Build HD display URLs for gallery/fullscreen: use HD when available (amazon_reference_image_hd for first image, or per-image hd_url)
        $imageDisplayUrls = [];
        $amazonHdUrl = $product->amazon_reference_image_hd;
        foreach ($product->images as $index => $image) {
            $url = $image->hd_url ?? null;
            if ($url === null && $index === 0 && $amazonHdUrl) {
                $url = $amazonHdUrl;
            }
            $imageDisplayUrls[] = $url ?? asset('storage/' . $image->image_path);
        }

        return view('toyshop.products.show', compact('product', 'relatedProducts', 'recommendedByPreferences', 'inWishlist', 'wishlistItem', 'imageDisplayUrls'));
    }
}
