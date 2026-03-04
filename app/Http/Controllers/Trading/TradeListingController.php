<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\TradeListing;
use App\Models\TradeListingImage;
use App\Models\Product;
use App\Models\UserProduct;
use App\Models\UserProductImage;
use App\Models\Category;
use App\Notifications\TradeListingSubmittedNotification;
use App\Services\TradeMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradeListingController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth')->except(['index', 'show']);
    }

    public function index(Request $request, TradeMatchingService $matchingService)
    {
        $query = TradeListing::with(['user', 'seller', 'product.images', 'product.category', 'userProduct.images', 'userProduct.category'])
            ->where('status', 'active');

        // Filters
        if ($request->filled('trade_type')) {
            $query->where('trade_type', $request->trade_type);
        }

        if ($request->filled('category_id')) {
            $query->where(function($q) use ($request) {
                $q->where('category_id', $request->category_id)
                  ->orWhereHas('product', fn($q) => $q->where('category_id', $request->category_id))
                  ->orWhereHas('userProduct', fn($q) => $q->where('category_id', $request->category_id));
            });
        }

        if ($request->filled('condition')) {
            $cond = $request->condition;
            $query->where(function($q) use ($cond) {
                $q->where('condition', $cond)
                  ->orWhereHas('product', fn($q) => $q->where('condition', $cond))
                  ->orWhereHas('userProduct', fn($q) => $q->where('condition', $cond));
            });
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhereHas('product', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('userProduct', function($q) use ($search) {
                      $q->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Sort
        $sort = $request->get('sort', 'newest');
        switch ($sort) {
            case 'newest':
                $query->orderBy('created_at', 'desc');
                break;
            case 'oldest':
                $query->orderBy('created_at', 'asc');
                break;
            case 'most_offers':
                $query->orderBy('offers_count', 'desc');
                break;
            case 'most_views':
                $query->orderBy('views_count', 'desc');
                break;
        }

        $listings = $query->paginate(12);
        $categories = Category::orderBy('name')->get();

        $suggestedListings = collect();
        if (Auth::check()) {
            $suggestedListings = $matchingService->getSuggestedListingsForUser(Auth::id(), 8);
        }

        return view('trading.index', compact('listings', 'categories', 'suggestedListings'));
    }

    public function myListings()
    {
        $listings = TradeListing::where('user_id', Auth::id())
            ->with(['product.images', 'userProduct.images', 'images', 'category'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('trading.listings.my-listings', compact('listings'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        $userProducts = UserProduct::where('user_id', Auth::id())
            ->where('status', 'available')
            ->with(['images', 'category'])
            ->get();
        $sellerProducts = collect();
        if (Auth::user()->isSeller() && Auth::user()->seller) {
            $sellerProducts = Product::where('seller_id', Auth::user()->seller->id)
                ->where('is_tradeable', true)
                ->where('trade_status', 'available_for_trade')
                ->where('status', 'active')
                ->with(['images', 'category'])
                ->get();
        }
        return view('trading.listings.create', compact('categories', 'userProducts', 'sellerProducts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'brand' => 'nullable|string|max:255',
            'description' => 'required|string',
            'category_id' => 'required|exists:categories,id',
            'condition' => 'nullable|string|in:new,used,refurbished',
            'location' => 'nullable|string|max:500',
            'location_lat' => 'nullable|numeric|between:-90,90',
            'location_lng' => 'nullable|numeric|between:-180,180',
            'meet_up_references' => 'nullable|string|max:1000',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'file|mimes:jpeg,png,jpg,webp|max:5120',
            'trade_type' => 'required|in:barter,barter_with_cash,cash',
            'cash_difference' => 'nullable|numeric|min:0',
            'product_type' => 'nullable|in:user_product,seller_product',
            'product_id' => 'nullable|integer',
            'user_product_id' => 'nullable|integer',
        ], [
            'images.required' => 'Please upload at least one image.',
            'images.*.mimes' => 'Each image must be JPEG, PNG, or WebP.',
            'images.*.max' => 'Each image must be 5 MB or smaller.',
        ]);

        DB::beginTransaction();
        try {
            $sellerId = null;
            if (Auth::user()->isSeller() && Auth::user()->seller) {
                $sellerId = Auth::user()->seller->id;
            }

            $cashDifference = $validated['trade_type'] === 'barter' ? null : ($validated['cash_difference'] ?? null);

            $productId = null;
            $userProductId = null;
            if (!empty($validated['product_type'])) {
                if ($validated['product_type'] === 'seller_product' && $sellerId && !empty($validated['product_id'])) {
                    $p = Product::where('id', $validated['product_id'])
                        ->where('seller_id', $sellerId)
                        ->where('is_tradeable', true)
                        ->where('trade_status', 'available_for_trade')
                        ->where('status', 'active')
                        ->first();
                    if ($p) {
                        $productId = $p->id;
                    }
                } elseif ($validated['product_type'] === 'user_product' && !empty($validated['user_product_id'])) {
                    $up = UserProduct::where('id', $validated['user_product_id'])
                        ->where('user_id', Auth::id())
                        ->where('status', 'available')
                        ->first();
                    if ($up) {
                        $userProductId = $up->id;
                    }
                }
            }

            $listing = TradeListing::create([
                'user_id' => Auth::id(),
                'seller_id' => $sellerId,
                'product_id' => $productId,
                'user_product_id' => $userProductId,
                'category_id' => $validated['category_id'],
                'title' => $validated['title'],
                'brand' => $validated['brand'] ?? null,
                'description' => $validated['description'],
                'condition' => $validated['condition'] ?? null,
                'location' => $validated['location'] ?? null,
                'location_lat' => isset($validated['location_lat']) ? (float) $validated['location_lat'] : null,
                'location_lng' => isset($validated['location_lng']) ? (float) $validated['location_lng'] : null,
                'meet_up_references' => $validated['meet_up_references'] ?? null,
                'trade_type' => $validated['trade_type'],
                'cash_difference' => $cashDifference,
                'desired_items' => null,
                'expires_at' => null,
                'status' => 'pending_approval',
            ]);

            $imageFiles = $request->file('images');
            if ($imageFiles && is_array($imageFiles)) {
                $displayOrder = 0;
                foreach ($imageFiles as $image) {
                    if ($image && $image->isValid()) {
                        $path = $image->store('trade-listings/' . Auth::id(), 'public');
                        $listing->images()->create([
                            'image_path' => $path,
                            'display_order' => $displayOrder++,
                        ]);
                    }
                }
            }

            // Auto-create UserProduct for listings without linked product (enables Make an Offer sync)
            if ($productId === null && $userProductId === null && $listing->images->isNotEmpty()) {
                $up = UserProduct::create([
                    'user_id' => Auth::id(),
                    'category_id' => $listing->category_id,
                    'name' => $listing->title,
                    'description' => $listing->description ?? '',
                    'brand' => $listing->brand,
                    'condition' => $listing->condition,
                    'status' => 'available',
                ]);
                $firstImg = $listing->images()->first();
                if ($firstImg) {
                    UserProductImage::create([
                        'user_product_id' => $up->id,
                        'image_path' => $firstImg->image_path,
                        'is_primary' => true,
                        'display_order' => 0,
                    ]);
                }
                $listing->update(['user_product_id' => $up->id]);
            }

            DB::commit();

            Auth::user()->notify(new TradeListingSubmittedNotification($listing));

            return redirect()->route('trading.index')
                ->with('success', 'Your listing has been submitted for admin approval. Check your notifications to view or edit it while it is pending.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Failed to create trade listing: ' . $e->getMessage()], 500);
            }
            return back()->withInput()->with('error', 'Failed to create trade listing: ' . $e->getMessage());
        }
    }

    public function show($id, TradeMatchingService $matchingService)
    {
        $listing = TradeListing::with([
            'user',
            'seller',
            'category',
            'images',
            'product.images',
            'product.category',
            'userProduct.images',
            'userProduct.category',
        ])->findOrFail($id);

        // Only active (or pending_trade) listings are visible to public; owner and admins can always view
        $isOwner = Auth::check() && $listing->user_id === Auth::id();
        $isAdmin = Auth::check() && Auth::user()->isAdmin();
        if (!in_array($listing->status, ['active', 'pending_trade']) && !$isOwner && !$isAdmin) {
            abort(404);
        }

        // Increment views only for active listings when not owner
        if ($listing->status === 'active' && !$isOwner) {
            $listing->increment('views_count');
        }

        $canMakeOffer = Auth::check() &&
            $listing->user_id !== Auth::id() &&
            $listing->canAcceptOffers();

        $suggestedListings = $matchingService->getSuggestedListingsForListing($listing->id, 6, Auth::check() ? Auth::id() : null);

        return view('trading.listings.show', compact('listing', 'canMakeOffer', 'suggestedListings'));
    }

    /**
     * Trade history for a listing: completed trades with proof (Barter/Barter+Cash: Trade 1 & 2 + proofs; Cash: listing + proof).
     */
    public function history($id)
    {
        $listing = TradeListing::where('user_id', Auth::id())
            ->with(['images', 'product.images', 'userProduct.images', 'category'])
            ->findOrFail($id);

        $trades = \App\Models\Trade::where('trade_listing_id', $listing->id)
            ->where('status', 'completed')
            ->with(['initiator', 'participant', 'tradeListing.images'])
            ->orderBy('completed_at', 'desc')
            ->get();

        return view('trading.listings.history', compact('listing', 'trades'));
    }

}
