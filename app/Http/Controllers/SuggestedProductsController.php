<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SuggestedProductsController extends Controller
{
    /**
     * Display suggested products based on user's category preferences
     */
    public function index()
    {
        $user = Auth::user();

        // If user hasn't selected categories yet, redirect to category selection
        if (!$user->hasSelectedCategories()) {
            return redirect()->route('category-preferences.show')
                ->with('info', 'Please select your toy category preferences first!');
        }

        // Get user's preferred categories
        $preferredCategoryIds = $user->categoryPreferences()->pluck('categories.id')->toArray();

        // Algorithm to suggest products:
        // 1. Products from preferred categories (weighted highest)
        // 2. Consider rating, sales count, and recency
        // 3. Mix of popular and new products
        
        $suggestedProducts = Product::with(['seller', 'category', 'images'])
            ->where('status', 'active')
            ->whereIn('category_id', $preferredCategoryIds)
            ->whereHas('seller', function($q) {
                $q->where('is_active', true)
                  ->where('verification_status', 'approved');
            })
            ->where(function($query) {
                $query->where('stock_quantity', '>', 0);
            })
            ->orderByRaw('
                CASE 
                    WHEN rating >= 4.5 THEN 1
                    WHEN rating >= 4.0 THEN 2
                    WHEN rating >= 3.5 THEN 3
                    ELSE 4
                END
            ')
            ->orderBy('sales_count', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit(24)
            ->get();

        // If we don't have enough products from preferred categories, 
        // add some popular products from other categories
        if ($suggestedProducts->count() < 12) {
            $additionalProducts = Product::with(['seller', 'category', 'images'])
                ->where('status', 'active')
                ->whereNotIn('category_id', $preferredCategoryIds)
                ->whereHas('seller', function($q) {
                    $q->where('is_active', true)
                      ->where('verification_status', 'approved');
                })
                ->where('stock_quantity', '>', 0)
                ->where('rating', '>=', 4.0)
                ->orderBy('sales_count', 'desc')
                ->orderBy('rating', 'desc')
                ->limit(12 - $suggestedProducts->count())
                ->get();

            $suggestedProducts = $suggestedProducts->merge($additionalProducts);
        }

        // Get category names for display
        $selectedCategories = $user->categoryPreferences()->pluck('name')->toArray();

        return view('suggested-products.index', compact('suggestedProducts', 'selectedCategories'));
    }
}
