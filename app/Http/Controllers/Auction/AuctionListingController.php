<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class AuctionListingController extends Controller
{
    public function create()
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')
                ->with('error', 'Membership required to create auction listings.');
        }

        $plan = $user->currentPlan();
        $isVip = $plan && (($plan->can_register_individual_seller ?? false) || ($plan->can_register_business_seller ?? false));
        if (! $isVip) {
            return redirect()->route('auction.index')
                ->with('error', 'VIP membership required to create auction listings.');
        }

        if (! $user->hasAnyApprovedAuctionSeller()) {
            return redirect()->route('auction.index')
                ->with('error', 'You must be an approved auction seller to create listings.');
        }

        $categories = Category::orderBy('name')->get();

        return view('auction.listings.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')
                ->with('error', 'Membership required to create auction listings.');
        }

        $plan = $user->currentPlan();
        $isVip = $plan && (($plan->can_register_individual_seller ?? false) || ($plan->can_register_business_seller ?? false));
        if (! $isVip) {
            return redirect()->route('auction.index')
                ->with('error', 'VIP membership required to create auction listings.');
        }

        if (! $user->hasAnyApprovedAuctionSeller()) {
            return redirect()->route('auction.index')
                ->with('error', 'You must be an approved auction seller to create listings.');
        }

        $request->merge([
            'category_ids' => array_values(array_filter(
                array_map('intval', (array) ($request->category_ids ?? [])),
                function ($x) {
                    return $x > 0;
                }
            )),
        ]);

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'condition' => 'required|in:new,like_new,good,fair',
            'starting_bid' => 'required|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'duration_hours' => 'required|integer|min:1|max:720',
            'category_ids' => 'nullable|array|max:3',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
            'images.max' => 'Maximum 10 images allowed.',
        ]);

        try {
            $verification = $user->approvedAuctionSellerVerifications()->first();
        $sellerType = $verification ? $verification->type : 'individual';

        $durationHours = min(720, max(1, (int) $request->duration_hours));
        $categoryIds = $request->category_ids ?? [];
        $categoryIds = array_slice(array_map('intval', array_filter($categoryIds)), 0, 3);
        $primaryCategoryId = $categoryIds[0] ?? null;

        $data = [
            'user_id' => $user->id,
            'seller_id' => null,
            'seller_type' => $sellerType,
            'product_id' => null,
            'user_product_id' => null,
            'category_id' => $primaryCategoryId,
            'title' => $request->title,
            'description' => $request->description,
            'condition' => $request->condition,
            'starting_bid' => $request->starting_bid,
            'reserve_price' => $request->filled('reserve_price') ? $request->reserve_price : null,
            'bid_increment' => $request->bid_increment,
            'duration_hours' => $durationHours,
            'start_at' => null,
            'end_at' => null,
            'status' => 'draft',
        ];
        if (Schema::hasColumn('auctions', 'category_ids')) {
            $data['category_ids'] = ! empty($categoryIds) ? $categoryIds : null;
        }
        $allowedColumns = Schema::getColumnListing('auctions');
        $data = array_intersect_key($data, array_flip($allowedColumns));
        $auction = Auction::create($data);

        $imageIndex = 0;
        $primaryIndex = min(max(0, (int) $request->thumbnail_index), 9);
        $hasDisplayOrder = Schema::hasTable('auction_images') && Schema::hasColumn('auction_images', 'display_order');
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('auction_images/' . $auction->id, 'public');
                $imgData = [
                    'auction_id' => $auction->id,
                    'image_path' => $path,
                    'is_primary' => $imageIndex === $primaryIndex,
                ];
                if ($hasDisplayOrder) {
                    $imgData['display_order'] = $imageIndex;
                }
                AuctionImage::create($imgData);
                $imageIndex++;
            }
        }

            return redirect()->route('auction.listings.index')
                ->with('success', 'Auction listing created successfully. It is saved as a draft.');
        } catch (Throwable $e) {
            Log::error('Auction listing store failed: '.$e->getMessage(), [
                'exception' => $e,
                'user_id' => $user->id,
            ]);

            return back()->withInput()->with('error', 'Unable to create listing. Please try again or contact support.');
        }
    }

    public function index()
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')
                ->with('error', 'Membership required to manage auction listings.');
        }

        $listings = Auction::where('user_id', $user->id)
            ->orderByDesc('updated_at')
            ->paginate(15);

        return view('auction.listings.index', compact('listings'));
    }

    public function edit(Auction $listing)
    {
        $user = Auth::user();

        if ($listing->user_id !== $user->id) {
            abort(403);
        }
        if (! $listing->isDraft() && ! $listing->isActive()) {
            return redirect()->route('auction.listings.index')
                ->with('error', 'Only draft or active listings can be edited.');
        }

        $categories = Category::orderBy('name')->get();

        return view('auction.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Auction $listing)
    {
        $user = Auth::user();

        if ($listing->user_id !== $user->id) {
            abort(403);
        }
        if (! $listing->isDraft() && ! $listing->isActive()) {
            return redirect()->route('auction.listings.index')
                ->with('error', 'Only draft or active listings can be edited.');
        }

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'condition' => 'required|in:new,like_new,good,fair',
            'category_ids' => 'nullable|array|max:3',
            'category_ids.*' => 'exists:categories,id',
        ];
        
        // Only allow changing pricing and duration if it's a draft
        if ($listing->isDraft()) {
            $rules['starting_bid'] = 'required|numeric|min:1';
            $rules['reserve_price'] = 'nullable|numeric|min:0';
            $rules['bid_increment'] = 'required|numeric|min:1';
            $rules['duration_hours'] = 'required|integer|min:1|max:720';
        }

        $hasImages = $listing->images()->exists();
        if ($request->hasFile('images')) {
            $rules['images'] = 'array|min:0|max:10';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:5120';
        } elseif (! $hasImages) {
            $rules['images'] = 'required|array|min:1|max:10';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:5120';
        }
        $request->validate($rules, [
            'images.required' => 'At least one image is required.',
            'images.max' => 'Maximum 10 images allowed.',
        ]);

        $categoryIds = $request->category_ids ?? [];
        $categoryIds = array_slice(array_map('intval', array_filter($categoryIds)), 0, 3);
        $primaryCategoryId = $categoryIds[0] ?? null;

        $updateData = [
            'category_id' => $primaryCategoryId,
            'title' => $request->title,
            'description' => $request->description,
            'condition' => $request->condition,
            'rejection_reason' => null,
        ];
        
        if ($listing->isDraft()) {
            $updateData['starting_bid'] = $request->starting_bid;
            $updateData['reserve_price'] = $request->filled('reserve_price') ? $request->reserve_price : null;
            $updateData['bid_increment'] = $request->bid_increment;
            $updateData['duration_hours'] = min(720, max(1, (int) $request->duration_hours));
        }

        if (Schema::hasColumn('auctions', 'category_ids')) {
            $updateData['category_ids'] = ! empty($categoryIds) ? $categoryIds : null;
        }
        $listing->update($updateData);

        $orderIds = $request->image_order ? array_filter(array_map('intval', explode(',', $request->image_order))) : [];
        $newFiles = $request->hasFile('images') ? $request->file('images') : [];
        $thumbnailIdx = max(0, (int) $request->thumbnail_index);

        $imgQuery = $listing->images();
        if (Schema::hasColumn('auction_images', 'display_order')) {
            $imgQuery->orderBy('display_order');
        }
        $imgQuery->orderByDesc('is_primary');
        $existingImages = $imgQuery->get()->keyBy('id');
        if (empty($orderIds)) {
            $orderIds = $existingImages->keys()->all();
        }
        $deleteIds = $existingImages->keys()->diff($orderIds)->all();
        AuctionImage::whereIn('id', $deleteIds)->delete();

        $displayOrder = 0;
        $listing->images()->update(['is_primary' => false]);

        $hasDisplayOrder = Schema::hasColumn('auction_images', 'display_order');
        foreach ($orderIds as $imgId) {
            $img = $existingImages->get($imgId);
            if ($img) {
                $upd = ['is_primary' => $displayOrder === $thumbnailIdx];
                if ($hasDisplayOrder) {
                    $upd['display_order'] = $displayOrder;
                }
                $img->update($upd);
                $displayOrder++;
            }
        }
        foreach (array_slice($newFiles, 0, max(0, 10 - $displayOrder)) as $file) {
            $path = $file->store('auction_images/' . $listing->id, 'public');
            $imgData = ['auction_id' => $listing->id, 'image_path' => $path, 'is_primary' => $displayOrder === $thumbnailIdx];
            if ($hasDisplayOrder) {
                $imgData['display_order'] = $displayOrder;
            }
            AuctionImage::create($imgData);
            $displayOrder++;
        }

        if ($listing->images()->count() < 1) {
            return back()->withInput()->withErrors(['images' => 'At least one image is required.']);
        }

        return redirect()->route('auction.listings.index')
            ->with('success', 'Listing updated successfully.');
    }

    public function destroy(Auction $listing)
    {
        $user = Auth::user();

        if ($listing->user_id !== $user->id) {
            abort(403);
        }

        if (! $listing->isDraft()) {
            return redirect()->route('auction.listings.index')
                ->with('error', 'Only draft listings can be deleted.');
        }

        // Delete images from storage
        foreach ($listing->images as $image) {
            Storage::disk('public')->delete($image->image_path);
        }
        $listing->images()->delete();

        // Delete the listing
        $listing->delete();

        return redirect()->route('auction.listings.index')
            ->with('success', 'Draft listing deleted successfully.');
    }

    public function submitForApproval(Auction $listing)
    {
        $user = Auth::user();

        if ($listing->user_id !== $user->id) {
            abort(403);
        }
        if (! $listing->isDraft()) {
            return redirect()->route('auction.listings.index')
                ->with('error', 'Only draft listings can be submitted for approval.');
        }

        $listing->update([
            'status' => 'pending_approval',
            'terms_accepted_at' => now(),
            'rejection_reason' => null,
        ]);

        return redirect()->route('auction.listings.index')
            ->with('success', 'Listing submitted for approval. An admin or moderator will review it shortly.');
    }
}
