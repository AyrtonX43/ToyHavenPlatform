<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\TradeListing;
use App\Models\TradeListingImage;
use App\Models\Product;
use App\Models\UserProduct;
use App\Models\Category;
use App\Notifications\TradeListingSubmittedNotification;
use App\Services\TradeMatchingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

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
            ->with(['product.images', 'userProduct.images'])
            ->orderBy('created_at', 'desc')
            ->paginate(12);

        return view('trading.listings.my-listings', compact('listings'));
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('trading.listings.create', compact('categories'));
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

            $listing = TradeListing::create([
                'user_id' => Auth::id(),
                'seller_id' => $sellerId,
                'product_id' => null,
                'user_product_id' => null,
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
            'activeOffers.offeredProduct.images',
            'activeOffers.offeredUserProduct.images',
            'activeOffers.offerer',
            'activeOffers.offererSeller',
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

        // Check if chat is requested
        $showChat = request()->has('chat') && Auth::check() && $listing->user_id !== Auth::id();

        $suggestedListings = $matchingService->getSuggestedListingsForListing($listing->id, 6);

        return view('trading.listings.show', compact('listing', 'canMakeOffer', 'showChat', 'suggestedListings'));
    }

    public function edit($id)
    {
        $listing = TradeListing::where('user_id', Auth::id())
            ->with(['product.images', 'userProduct.images', 'images', 'category'])
            ->findOrFail($id);

        if (!in_array($listing->status, ['active', 'pending_approval'])) {
            return redirect()->route('trading.listings.show', $listing->id)
                ->with('error', 'Cannot edit this listing.');
        }

        $userProducts = UserProduct::where('user_id', Auth::id())
            ->where('status', 'available')
            ->orWhere('id', $listing->user_product_id)
            ->with(['images'])
            ->get();

        $sellerProducts = collect();
        if (Auth::user()->isSeller() && Auth::user()->seller) {
            $sellerProducts = Product::where('seller_id', Auth::user()->seller->id)
                ->where(function($q) use ($listing) {
                    $q->where('is_tradeable', true)
                      ->where('trade_status', 'available_for_trade')
                      ->orWhere('id', $listing->product_id);
                })
                ->where('status', 'active')
                ->with(['images'])
                ->get();
        }

        $categories = Category::orderBy('name')->get();

        return view('trading.listings.edit', compact('listing', 'userProducts', 'sellerProducts', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $listing = TradeListing::where('user_id', Auth::id())->findOrFail($id);

        if (!in_array($listing->status, ['active', 'pending_approval'])) {
            return redirect()->route('trading.listings.show', $listing->id)
                ->with('error', 'Cannot update this listing.');
        }

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
            'trade_type' => 'required|in:barter,barter_with_cash,cash',
            'cash_difference' => 'nullable|numeric|min:0',
            'desired_items' => 'nullable|array',
            'expires_at' => 'nullable|date|after:today',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'exists:trade_listing_images,id',
        ]);

        $updateData = [
            'title' => $validated['title'],
            'brand' => $validated['brand'] ?? null,
            'description' => $validated['description'],
            'category_id' => $validated['category_id'],
            'condition' => $validated['condition'] ?? null,
            'location' => $validated['location'] ?? null,
            'location_lat' => isset($validated['location_lat']) ? (float) $validated['location_lat'] : null,
            'location_lng' => isset($validated['location_lng']) ? (float) $validated['location_lng'] : null,
            'meet_up_references' => $validated['meet_up_references'] ?? null,
            'trade_type' => $validated['trade_type'],
            'cash_difference' => $validated['trade_type'] === 'barter' ? null : ($validated['cash_difference'] ?? null),
            'desired_items' => $validated['desired_items'] ?? null,
            'expires_at' => $validated['expires_at'] ?? null,
        ];

        // Re-approval required when editing an active listing
        if ($listing->status === 'active') {
            $updateData['status'] = 'pending_approval';
        }

        $listing->update($updateData);

        if ($request->has('remove_images')) {
            foreach ($request->remove_images as $imageId) {
                $img = $listing->images()->find($imageId);
                if ($img) {
                    Storage::disk('public')->delete($img->image_path);
                    $img->delete();
                }
            }
        }

        if ($request->hasFile('images')) {
            $maxOrder = $listing->images()->max('display_order') ?? -1;
            foreach ($request->file('images') as $image) {
                $path = $image->store('trade-listings/' . Auth::id(), 'public');
                $listing->images()->create([
                    'image_path' => $path,
                    'display_order' => ++$maxOrder,
                ]);
            }
        }

        $totalImages = $listing->images()->count();
        $minImages = $listing->status === 'pending_approval' ? 1 : 4;
        if ($totalImages < $minImages) {
            return back()->withInput()->with('error', 'Listing must have between ' . $minImages . ' and 10 images. Currently has ' . $totalImages . '.');
        }
        if ($totalImages > 10) {
            return back()->withInput()->with('error', 'Listing cannot have more than 10 images. Please remove some.');
        }

        return redirect()->route('trading.listings.show', $listing->id)
            ->with('success', 'Trade listing updated successfully!');
    }

    public function destroy($id)
    {
        $listing = TradeListing::where('user_id', Auth::id())->findOrFail($id);

        if ($listing->status === 'pending_trade') {
            return redirect()->route('trading.listings.my')
                ->with('error', 'Cannot delete listing that has an active trade.');
        }

        DB::beginTransaction();
        try {
            // Update product status back to available
            if ($listing->product_id) {
                Product::where('id', $listing->product_id)->update(['trade_status' => 'available_for_trade']);
            } elseif ($listing->user_product_id) {
                UserProduct::where('id', $listing->user_product_id)->update(['status' => 'available']);
            }

            $listing->update(['status' => 'cancelled']);
            DB::commit();

            return redirect()->route('trading.listings.my')
                ->with('success', 'Trade listing cancelled successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to cancel listing: ' . $e->getMessage());
        }
    }
}
