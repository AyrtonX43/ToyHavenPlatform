<?php

namespace App\Http\Controllers;

use App\Models\Auction;
use App\Models\Product;
use App\Models\Seller;
use App\Models\TradeListing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SearchController extends Controller
{
    /**
     * Real-time search suggest: products, business pages, trade listings, and auctions (JSON for dropdown)
     */
    public function suggest(Request $request)
    {
        $q = trim($request->get('q', ''));
        if (strlen($q) < 2) {
            return response()->json([
                'products' => [], 
                'businesses' => [], 
                'trades' => [], 
                'auctions' => []
            ]);
        }

        // Toyshop Products
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
            ->limit(5)
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
                    'type' => 'product',
                ];
            });

        // Business Stores
        $businesses = Seller::where('is_active', true)
            ->where('verification_status', 'approved')
            ->where(function ($query) use ($q) {
                $query->where('business_name', 'like', '%' . $q . '%')
                    ->orWhere('description', 'like', '%' . $q . '%');
            })
            ->orderBy('business_name')
            ->limit(3)
            ->get()
            ->map(function ($s) {
                return [
                    'id' => $s->id,
                    'name' => $s->business_name,
                    'slug' => $s->business_slug,
                    'url' => route('toyshop.business.show', $s->business_slug),
                    'type' => 'business',
                    'is_verified' => $s->is_verified_shop,
                ];
            });

        // Trade Listings
        $trades = TradeListing::with(['images', 'product.images', 'userProduct.images'])
            ->where('status', 'active')
            ->where(function($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                  ->orWhere('description', 'like', "%{$q}%")
                  ->orWhereHas('product', function($prodQuery) use ($q) {
                      $prodQuery->where('name', 'like', "%{$q}%");
                  })
                  ->orWhereHas('userProduct', function($userProdQuery) use ($q) {
                      $userProdQuery->where('name', 'like', "%{$q}%");
                  });
            })
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(function ($t) {
                $img = $t->images->first() ?? ($t->product ? $t->product->images->first() : null) ?? ($t->userProduct ? $t->userProduct->images->first() : null);
                return [
                    'id' => $t->id,
                    'name' => $t->title,
                    'url' => route('trading.listings.show', $t->id),
                    'image' => $img ? asset('storage/' . $img->image_path) : null,
                    'type' => 'trade',
                ];
            });

        // Auction Listings
        $auctions = Auction::with(['images'])
            ->where('status', 'live')
            ->where(function ($query) use ($q) {
                $query->where('title', 'like', "%{$q}%")
                    ->orWhere('description', 'like', "%{$q}%");
            })
            ->orderByDesc('created_at')
            ->limit(3)
            ->get()
            ->map(function ($a) {
                $img = $a->images->first();
                return [
                    'id' => $a->id,
                    'name' => $a->title,
                    'starting_bid' => $a->starting_bid,
                    'url' => route('auctions.show', $a->id),
                    'image' => $img ? asset('storage/' . $img->path) : null,
                    'type' => 'auction',
                ];
            });

        return response()->json([
            'products' => $products,
            'businesses' => $businesses,
            'trades' => $trades,
            'auctions' => $auctions,
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
            $tradeQuery = TradeListing::with(['user', 'seller', 'images', 'product.images', 'product.category', 'userProduct.images', 'userProduct.category'])
                ->where('status', 'active')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->where(function($tradeQ) use ($query) {
                    $tradeQ->where('title', 'like', "%{$query}%")
                      ->orWhere('description', 'like', "%{$query}%")
                      ->orWhereHas('product', function($prodQ) use ($query) {
                          $prodQ->where('name', 'like', "%{$query}%");
                      })
                      ->orWhereHas('userProduct', function($userProdQ) use ($query) {
                          $userProdQ->where('name', 'like', "%{$query}%");
                      });
                })
                ->orderBy('created_at', 'desc')
                ->limit(12)
                ->get();
            
            $results['trade'] = $tradeQuery;
        }

        // Search Auction Listings (members only)
        $user = auth()->user();
        $canSeeAuctions = $user && $user->hasActiveMembership();
        if (($type === 'all' || $type === 'auction') && $canSeeAuctions) {
            $auctionQuery = Auction::with(['images', 'category'])
                ->where('status', 'live')
                ->where(function ($q) use ($query) {
                    $q->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%");
                })
                ->orderByDesc('created_at')
                ->limit(12)
                ->get();
            $results['auction'] = $auctionQuery;
        }
        if (! $canSeeAuctions) {
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
