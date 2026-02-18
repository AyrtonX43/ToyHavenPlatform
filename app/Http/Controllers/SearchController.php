<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Seller;
use App\Models\TradeListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Real-time search suggest: products and business pages (JSON for dropdown)
     */
    public function suggest(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json(['products' => [], 'businesses' => []]);
        }

        $products = Product::with(['images', 'seller'])
            ->where('status', 'active')
            ->whereHas('seller', function ($sq) {
                $sq->where('is_active', true)->where('verification_status', 'approved');
            })
            ->where(function ($query) use ($q) {
                $query->where('name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%')
                    ->orWhere('brand', 'like', '%' . $q . '%');
            })
            ->orderBy('name')
            ->limit(8)
            ->get()
            ->map(function ($p) {
                $img = $p->images->first();
                return [
                    'id' => $p->id,
                    'name' => $p->name,
                    'price' => $p->price,
                    'slug' => $p->slug,
                    'url' => route('toyshop.products.show', $p->slug),
                    'image' => $img ? asset('storage/' . $img->image_path) : null,
                ];
            });

        $businesses = Seller::where('is_active', true)
            ->where('verification_status', 'approved')
            ->where(function ($query) use ($q) {
                $query->where('business_name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            })
            ->orderBy('business_name')
            ->limit(5)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->business_name,
                    'slug' => $s->business_slug,
                    'url' => route('toyshop.business.show', $s->business_slug),
                ];
            });

        return response()->json([
            'products' => $products,
            'businesses' => $businesses,
        ]);
    }

    /**
     * Unified search across toyshop, trade, and auction
     */
    public function search(Request $request)
    {
        $query = $request->get('q', '');
        $type = $request->get('type', 'all'); // all, toyshop, trade, auction
        
        if (empty($query)) {
            return redirect()->route('home');
        }

        $results = [
            'toyshop' => collect(),
            'trade' => collect(),
            'auction' => collect(),
        ];

        // Search Toyshop Products
        if ($type === 'all' || $type === 'toyshop') {
            $toyshopQuery = Product::with(['seller', 'category', 'images'])
                ->where('status', 'active')
                ->whereHas('seller', function($q) {
                    $q->where('is_active', true)
                      ->where('verification_status', 'approved');
                })
                ->where(function($q) use ($query) {
                    $q->where('name', 'like', '%' . $query . '%')
                      ->orWhere('description', 'like', '%' . $query . '%')
                      ->orWhere('brand', 'like', '%' . $query . '%');
                })
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            $results['toyshop'] = $toyshopQuery;
        }

        // Search Business Pages (Stores)
        if ($type === 'all' || $type === 'businesses') {
            $businessQuery = Seller::where('is_active', true)
                ->where('verification_status', 'approved')
                ->where(function ($q) use ($query) {
                    $q->where('business_name', 'like', '%' . $query . '%')
                        ->orWhere('description', 'like', '%' . $query . '%');
                })
                ->orderBy('business_name')
                ->limit(12)
                ->get();
            $results['businesses'] = $businessQuery;
        }

        // Search Trade Listings
        if ($type === 'all' || $type === 'trade') {
            $tradeQuery = TradeListing::with(['user', 'seller', 'product.images', 'product.category', 'userProduct.images', 'userProduct.category'])
                ->where('status', 'active')
                ->where(function($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhereHas('product', function($q) use ($query) {
                          $q->where('name', 'like', "%{$query}%");
                      })
                      ->orWhereHas('userProduct', function($q) use ($query) {
                          $q->where('name', 'like', "%{$query}%");
                      });
                })
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            $results['trade'] = $tradeQuery;
        }

        // Search Auction (placeholder for future implementation)
        if ($type === 'all' || $type === 'auction') {
            // Auction functionality will be implemented later
            $results['auction'] = collect();
        }

        // Count totals
        $counts = [
            'toyshop' => $results['toyshop']->count(),
            'businesses' => $results['businesses']->count(),
            'trade' => $results['trade']->count(),
            'auction' => $results['auction']->count(),
            'total' => $results['toyshop']->count() + $results['businesses']->count() + $results['trade']->count() + $results['auction']->count(),
        ];

        return view('search.results', compact('query', 'results', 'counts', 'type'));
    }
}
