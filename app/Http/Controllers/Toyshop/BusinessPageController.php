<?php

namespace App\Http\Controllers\Toyshop;

use App\Http\Controllers\Controller;
use App\Models\Seller;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BusinessPageController extends Controller
{
    public function show($slug)
    {
        $seller = Seller::where('business_slug', $slug)
            ->where('verification_status', 'approved')
            ->where('is_active', true)
            ->with(['products' => function($q) {
                $q->where('status', 'active');
            }, 'products.images', 'reviews.user', 'pageSettings', 'socialLinks'])
            ->firstOrFail();

        // Double check - if somehow seller is not active, return 404
        if (!$seller->is_active) {
            abort(404, 'Business page not found.');
        }

        $pageSettings = $seller->pageSettings;
        $socialLinks = $seller->socialLinks ?? collect();

        // Get seller statistics
        $stats = [
            'total_products' => $seller->products()->where('status', 'active')->count(),
            'total_sales' => $seller->total_sales,
            'rating' => $seller->rating,
            'total_reviews' => $seller->total_reviews,
        ];

        // Get products (only active products from active seller)
        $products = $seller->products()
            ->where('status', 'active')
            ->with('images')
            ->paginate(12);

        // Get recent reviews
        $recentReviews = $seller->reviews()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Wishlist data for authenticated users
        $wishlistProductIds = [];
        $wishlistItems = [];
        if (Auth::check()) {
            $wishlists = Wishlist::where('user_id', Auth::id())->get();
            $wishlistProductIds = $wishlists->pluck('product_id')->toArray();
            $wishlistItems = $wishlists->keyBy('product_id')->toArray();
        }

        return view('toyshop.business.show', compact('seller', 'pageSettings', 'socialLinks', 'stats', 'products', 'recentReviews', 'wishlistProductIds', 'wishlistItems'));
    }
}
