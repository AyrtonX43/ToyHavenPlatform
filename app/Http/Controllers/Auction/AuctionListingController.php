<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

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

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'condition' => 'required|in:new,like_new,good,fair',
            'starting_bid' => 'required|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'scheduled_end_at' => 'required|date|after:now',
            'category_ids' => 'nullable|array|max:3',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'required|array|min:1|max:10',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
            'primary_index' => 'nullable|integer|min:0',
        ], [
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
            'images.max' => 'Maximum 10 images allowed.',
        ]);

        $verification = $user->approvedAuctionSellerVerifications()->first();
        $sellerType = $verification ? $verification->type : 'individual';

        $scheduledEndAt = \Carbon\Carbon::parse($request->scheduled_end_at);
        $durationHours = (int) max(1, now()->diffInHours($scheduledEndAt, false));

        $auction = Auction::create([
            'user_id' => $user->id,
            'seller_id' => null,
            'seller_type' => $sellerType,
            'product_id' => null,
            'user_product_id' => null,
            'category_id' => $request->category_ids[0] ?? null,
            'title' => $request->title,
            'description' => $request->description,
            'condition' => $request->condition,
            'starting_bid' => $request->starting_bid,
            'reserve_price' => $request->filled('reserve_price') ? $request->reserve_price : null,
            'bid_increment' => $request->bid_increment,
            'duration_hours' => $durationHours,
            'scheduled_end_at' => $scheduledEndAt,
            'start_at' => null,
            'end_at' => null,
            'status' => 'draft',
        ]);

        $categoryIds = array_slice($request->category_ids ?? [], 0, 3);
        if (! empty($categoryIds)) {
            $auction->categories()->sync(
                collect($categoryIds)->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->toArray()
            );
        }

        $primaryIndex = (int) ($request->primary_index ?? 0);
        $imageIndex = 0;
        if ($request->hasFile('images')) {
            $files = $request->file('images');
            foreach ($files as $idx => $image) {
                $path = $image->store('auction_images/' . $auction->id, 'public');
                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'image_path' => $path,
                    'is_primary' => $idx === $primaryIndex,
                    'sort_order' => $idx,
                ]);
                $imageIndex++;
            }
        }

        return redirect()->route('auction.listings.index')
            ->with('success', 'Auction listing created successfully. It is saved as a draft.');
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
        if (! $listing->isDraft()) {
            return redirect()->route('auction.listings.index')
                ->with('error', 'Only draft listings can be edited.');
        }

        $listing->load('categories');
        $categories = Category::orderBy('name')->get();

        return view('auction.listings.edit', compact('listing', 'categories'));
    }

    public function update(Request $request, Auction $listing)
    {
        $user = Auth::user();

        if ($listing->user_id !== $user->id) {
            abort(403);
        }
        if (! $listing->isDraft()) {
            return redirect()->route('auction.listings.index')
                ->with('error', 'Only draft listings can be edited.');
        }

        $keepIds = array_filter(array_map('intval', explode(',', $request->keep_image_ids ?? '')));
        $deleteIds = array_filter(array_map('intval', explode(',', $request->delete_image_ids ?? '')));
        $newFilesCount = $request->hasFile('images') ? count($request->file('images')) : 0;
        $totalAfterUpdate = count($keepIds) + $newFilesCount;

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'condition' => 'required|in:new,like_new,good,fair',
            'starting_bid' => 'required|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'scheduled_end_at' => 'required|date|after:now',
            'category_ids' => 'nullable|array|max:3',
            'category_ids.*' => 'exists:categories,id',
            'primary_index' => 'nullable|integer|min:0',
        ];
        if ($totalAfterUpdate < 1) {
            $rules['images'] = 'required|array|min:1';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:5120';
        } elseif ($request->hasFile('images')) {
            $rules['images'] = 'array|max:10';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:5120';
        }
        $request->validate($rules, ['images.required' => 'At least one image is required.']);

        if ($totalAfterUpdate > 10) {
            return back()->withInput()->withErrors(['images' => 'Maximum 10 images allowed.']);
        }

        $scheduledEndAt = \Carbon\Carbon::parse($request->scheduled_end_at);
        $durationHours = (int) max(1, now()->diffInHours($scheduledEndAt, false));

        $listing->update([
            'category_id' => ($request->category_ids ?? [])[0] ?? null,
            'title' => $request->title,
            'description' => $request->description,
            'condition' => $request->condition,
            'starting_bid' => $request->starting_bid,
            'reserve_price' => $request->filled('reserve_price') ? $request->reserve_price : null,
            'bid_increment' => $request->bid_increment,
            'duration_hours' => $durationHours,
            'scheduled_end_at' => $scheduledEndAt,
            'rejection_reason' => null,
        ]);

        $categoryIds = array_slice($request->category_ids ?? [], 0, 3);
        $listing->categories()->sync(
            collect($categoryIds)->mapWithKeys(fn ($id, $i) => [$id => ['sort_order' => $i]])->toArray()
        );

        foreach ($deleteIds as $imgId) {
            $img = AuctionImage::where('auction_id', $listing->id)->find($imgId);
            if ($img) {
                Storage::disk('public')->delete($img->image_path);
                $img->delete();
            }
        }

        $primaryIndex = (int) ($request->primary_index ?? 0);
        $keptImages = collect();
        if (! empty($keepIds)) {
            $byId = AuctionImage::where('auction_id', $listing->id)->whereIn('id', $keepIds)->get()->keyBy('id');
            $keptImages = collect($keepIds)->map(fn ($id) => $byId->get($id))->filter()->values();
        }
        $newFiles = $request->hasFile('images') ? $request->file('images') : [];
        $sortOrder = 0;
        foreach ($keptImages as $idx => $img) {
            $img->update(['is_primary' => $idx === $primaryIndex, 'sort_order' => $sortOrder++]);
        }
        foreach ($newFiles as $idx => $file) {
            $path = $file->store('auction_images/' . $listing->id, 'public');
            $isPrimary = ($keptImages->count() + $idx) === $primaryIndex;
            AuctionImage::create([
                'auction_id' => $listing->id,
                'image_path' => $path,
                'is_primary' => $isPrimary,
                'sort_order' => $sortOrder++,
            ]);
        }

        return redirect()->route('auction.listings.index')
            ->with('success', 'Listing updated successfully.');
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
