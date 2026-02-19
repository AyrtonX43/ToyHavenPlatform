<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Show the welcome / homepage with ToyStore-style data.
     */
    public function __invoke(Request $request)
    {
        $categories = Category::where('is_active', true)
            ->orderBy('display_order')
            ->orderBy('name')
            ->get();

        // Guest homepage: show all products (minimum 5)
        $featuredProducts = Product::with(['seller', 'category', 'images'])
            ->where('status', 'active')
            ->whereHas('seller', function ($q) {
                $q->where('is_active', true)->where('verification_status', 'approved');
            })
            ->orderBy('created_at', 'desc')
            ->limit(12)
            ->get();

        return view('welcome', [
            'categories' => $categories,
            'featuredProducts' => $featuredProducts,
        ]);
    }
}
