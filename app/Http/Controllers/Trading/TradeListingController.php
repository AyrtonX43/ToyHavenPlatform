<?php

namespace App\Http\Controllers\Trading;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\SavedTradeListing;
use App\Models\TradeListing;
use App\Models\UserProduct;
use App\Notifications\TradeListingSubmittedNotification;
use App\Services\TradeListingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TradeListingController extends Controller
{
    public function __construct(
        protected TradeListingService $listingService
    ) {
        $this->middleware('auth')->except(['index', 'show']);
        $this->middleware('trade.not.suspended')->only(['create', 'store', 'edit', 'update', 'destroy', 'myListings']);
    }

    public function index(Request $request)
    {
        $query = TradeListing::with(['user', 'images', 'category', 'product.images', 'userProduct.images'])
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            });

        if ($request->filled('trade_type')) {
            $query->where('trade_type', $request->trade_type);
        }
        if ($request->filled('category_id')) {
            $catId = (int) $request->category_id;
            $query->where(function ($q) use ($catId) {
                $q->where('category_id', $catId)
                    ->orWhereJsonContains('category_ids', $catId);
            });
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', "%{$s}%")
                    ->orWhere('description', 'like', "%{$s}%")
                    ->orWhere('brand', 'like', "%{$s}%");
            });
        }

        $listings = $query->orderByDesc('created_at')->paginate(24);
        $categories = Category::orderBy('name')->get();

        return view('trading.index', compact('listings', 'categories'));
    }

    public function show($id)
    {
        $listing = TradeListing::with(['user', 'images', 'category', 'product.images', 'userProduct.images', 'product.category', 'userProduct.category'])
            ->findOrFail($id);

        if ($listing->status !== 'active' && $listing->user_id !== Auth::id()) {
            abort(404);
        }

        $listing->increment('views_count');

        $isSaved = Auth::check() && SavedTradeListing::where('user_id', Auth::id())
            ->where('trade_listing_id', $listing->id)->exists();

        $offerContext = null;
        $offerId = request()->query('offer_id');
        if (Auth::check() && $offerId && \Illuminate\Support\Facades\Schema::hasColumn('trade_offers', 'offered_trade_listing_id')) {
            $offer = \App\Models\TradeOffer::with(['tradeListing', 'offerer'])->find($offerId);
            if ($offer && $offer->status === 'pending'
                && $offer->tradeListing->user_id === Auth::id()
                && (int) ($offer->offered_trade_listing_id ?? 0) === (int) $listing->id) {
                $offerContext = $offer;
            }
        }

        return view('trading.listings.show', compact('listing', 'isSaved', 'offerContext'));
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
            'description' => 'required|string|max:5000',
            'category_ids' => 'required|array|min:1|max:3',
            'category_ids.*' => 'exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'condition' => 'required|in:new,like_new,good,fair,used',
            'location' => 'nullable|string|max:500',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'meetup_radius_km' => 'nullable|numeric|min:0.5|max:100',
            'meet_up_references' => 'nullable|string|max:500',
            'trade_type' => 'required|in:exchange,exchange_with_cash,cash',
            'desired_items' => 'nullable|array',
            'desired_items.*' => 'string|max:255',
            'cash_amount' => 'nullable|numeric|min:0',
            'user_product_id' => 'nullable|exists:user_products,id',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail_index' => 'nullable|integer|min:0',
        ]);

        $userId = Auth::id();
        $sellerId = Auth::user()->seller?->id;
        $categoryIds = $validated['category_ids'];
        $primaryCategoryId = $categoryIds[0];

        $data = [
            'user_id' => $userId,
            'seller_id' => $sellerId,
            'product_id' => null,
            'user_product_id' => $validated['user_product_id'] ?? null,
            'category_id' => $primaryCategoryId,
            'category_ids' => $categoryIds,
            'title' => $validated['title'],
            'brand' => $validated['brand'] ?? null,
            'description' => $validated['description'],
            'condition' => $validated['condition'],
            'location' => $validated['location'] ?? null,
            'location_lat' => $validated['location_lat'] ?? null,
            'location_lng' => $validated['location_lng'] ?? null,
            'meetup_radius_km' => $validated['meetup_radius_km'] ?? null,
            'meet_up_references' => $validated['meet_up_references'] ?? null,
            'trade_type' => $validated['trade_type'],
            'desired_items' => $validated['desired_items'] ?? null,
            'cash_amount' => $validated['trade_type'] === 'exchange' ? null : ($validated['cash_amount'] ?? null),
            'status' => 'pending_approval',
        ];

        DB::beginTransaction();
        try {
            $listing = $this->listingService->create($data);

            if ($request->hasFile('images')) {
                $thumbIdx = (int) ($request->input('thumbnail_index', 0));
                foreach ($request->file('images') as $i => $file) {
                    $path = $file->store('trade-listings/' . $listing->id, 'public');
                    $listing->images()->create([
                        'image_path' => $path,
                        'display_order' => $i,
                        'is_thumbnail' => $thumbIdx === $i,
                    ]);
                }
            }

            try {
                $admins = \App\Models\User::where('role', 'admin')->get();
                $moderators = \App\Models\User::where('role', 'moderator')->get();
                foreach ($admins->merge($moderators) as $mod) {
                    $mod->notify(new TradeListingSubmittedNotification($listing));
                }
            } catch (\Throwable $notifyEx) {
                Log::warning('Trade listing notification failed', ['message' => $notifyEx->getMessage()]);
            }

            DB::commit();
            return redirect()->route('trading.listings.my')
                ->with('success', 'Your listing has been submitted for review. You will be notified once it is approved.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Trade listing create failed', ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withInput()->with('error', 'Failed to create listing. Please try again.');
        }
    }

    public function edit($id)
    {
        $listing = TradeListing::where('user_id', Auth::id())->with('images')->findOrFail($id);
        if ($listing->status !== 'pending_approval' && $listing->status !== 'active') {
            return redirect()->route('trading.listings.my')->with('error', 'This listing cannot be edited.');
        }
        if ($listing->status === 'pending_deal') {
            return redirect()->route('trading.listings.my')->with('error', 'This listing has an active deal.');
        }

        $this->authorize('update', $listing);

        $categories = Category::orderBy('name')->get();
        return view('trading.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $listing = TradeListing::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $listing);

        if ($listing->status !== 'pending_approval' && $listing->status !== 'active') {
            return back()->with('error', 'This listing cannot be edited.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_ids' => 'required|array|min:1|max:3',
            'category_ids.*' => 'exists:categories,id',
            'brand' => 'nullable|string|max:100',
            'condition' => 'required|in:new,like_new,good,fair,used',
            'location' => 'nullable|string|max:500',
            'location_lat' => 'nullable|numeric',
            'location_lng' => 'nullable|numeric',
            'meetup_radius_km' => 'nullable|numeric|min:0.5|max:100',
            'meet_up_references' => 'nullable|string|max:500',
            'trade_type' => 'required|in:exchange,exchange_with_cash,cash',
            'desired_items' => 'nullable|array',
            'cash_amount' => 'nullable|numeric|min:0',
            'images' => 'nullable|array|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'thumbnail_index' => 'nullable|integer|min:0',
        ]);

        $categoryIds = $validated['category_ids'];
        $primaryCategoryId = $categoryIds[0];
        $data = [
            'category_id' => $primaryCategoryId,
            'category_ids' => $categoryIds,
            'title' => $validated['title'],
            'brand' => $validated['brand'] ?? null,
            'description' => $validated['description'],
            'condition' => $validated['condition'],
            'location' => $validated['location'] ?? null,
            'location_lat' => $validated['location_lat'] ?? null,
            'location_lng' => $validated['location_lng'] ?? null,
            'meetup_radius_km' => $validated['meetup_radius_km'] ?? null,
            'meet_up_references' => $validated['meet_up_references'] ?? null,
            'trade_type' => $validated['trade_type'],
            'desired_items' => $validated['desired_items'] ?? null,
            'cash_amount' => $validated['trade_type'] === 'exchange' ? null : ($validated['cash_amount'] ?? null),
        ];

        DB::beginTransaction();
        try {
            $this->listingService->update($listing, $data);

            if ($request->hasFile('images') && count($request->file('images')) > 0) {
                foreach ($listing->images as $img) {
                    Storage::disk('public')->delete($img->image_path);
                }
                $listing->images()->delete();
                $thumbIdx = (int) ($request->input('thumbnail_index', 0));
                foreach ($request->file('images') as $i => $file) {
                    $path = $file->store('trade-listings/' . $listing->id, 'public');
                    $listing->images()->create([
                        'image_path' => $path,
                        'display_order' => $i,
                        'is_thumbnail' => $thumbIdx === $i,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('trading.listings.show', $listing->id)
                ->with('success', 'Listing updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Failed to update listing.');
        }
    }

    public function savedListings(Request $request)
    {
        $saved = SavedTradeListing::where('user_id', Auth::id())
            ->with(['tradeListing.images', 'tradeListing.user', 'tradeListing.category'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('trading.listings.saved-listings', compact('saved'));
    }

    public function save($id)
    {
        $listing = TradeListing::findOrFail($id);

        if (SavedTradeListing::where('user_id', Auth::id())->where('trade_listing_id', $id)->exists()) {
            return back()->with('info', 'Listing already saved.');
        }

        SavedTradeListing::create(['user_id' => Auth::id(), 'trade_listing_id' => $id]);
        return back()->with('success', 'Listing saved to your saved list.');
    }

    public function unsave($id)
    {
        SavedTradeListing::where('user_id', Auth::id())->where('trade_listing_id', $id)->delete();
        return back()->with('success', 'Listing removed from saved list.');
    }

    public function myListings(Request $request)
    {
        $listings = TradeListing::where('user_id', Auth::id())
            ->with(['images', 'category'])
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('trading.listings.my-listings', compact('listings'));
    }

    public function markAsSold($id)
    {
        $listing = TradeListing::where('user_id', Auth::id())->findOrFail($id);
        $this->authorize('update', $listing);

        if ($listing->status !== 'active') {
            return back()->with('error', 'Only active listings can be marked as sold.');
        }

        $this->listingService->markCompleted($listing);
        return back()->with('success', 'Listing marked as sold.');
    }

    public function destroy($id)
    {
        $listing = TradeListing::where('user_id', Auth::id())->with('images')->findOrFail($id);
        $this->authorize('delete', $listing);

        if ($listing->status === 'pending_deal') {
            return back()->with('error', 'Cannot delete a listing with an active deal.');
        }

        DB::beginTransaction();
        try {
            $this->listingService->returnProductToAvailable($listing);
            foreach ($listing->images as $img) {
                Storage::disk('public')->delete($img->image_path);
            }
            $listing->images()->delete();
            $listing->offers()->delete();
            $listing->delete();
            DB::commit();
            return redirect()->route('trading.listings.my')->with('success', 'Listing deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->with('error', 'Failed to delete listing.');
        }
    }
}
