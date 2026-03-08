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
            'duration_hours' => 'required|integer|min:1|max:720',
            'category_id' => 'nullable|exists:categories,id',
            'images' => 'required|array|min:1',
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:5120',
        ], [
            'images.required' => 'At least one image is required.',
            'images.min' => 'At least one image is required.',
        ]);

        $verification = $user->approvedAuctionSellerVerifications()->first();
        $sellerType = $verification ? $verification->type : 'individual';

        $durationHours = (int) $request->duration_hours;

        $auction = Auction::create([
            'user_id' => $user->id,
            'seller_id' => null,
            'seller_type' => $sellerType,
            'product_id' => null,
            'user_product_id' => null,
            'category_id' => $request->category_id ?: null,
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
        ]);

        $imageIndex = 0;
        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('auction_images/' . $auction->id, 'public');
                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'image_path' => $path,
                    'is_primary' => $imageIndex === 0,
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

        $rules = [
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'condition' => 'required|in:new,like_new,good,fair',
            'starting_bid' => 'required|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'duration_hours' => 'required|integer|min:1|max:720',
            'category_id' => 'nullable|exists:categories,id',
        ];
        $hasImages = $listing->images()->exists();
        if ($request->hasFile('images')) {
            $rules['images'] = 'array|min:1';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:5120';
        } elseif (! $hasImages) {
            $rules['images'] = 'required|array|min:1';
            $rules['images.*'] = 'image|mimes:jpeg,png,jpg,webp|max:5120';
        }
        $request->validate($rules, ['images.required' => 'At least one image is required.']);

        $listing->update([
            'category_id' => $request->category_id ?: null,
            'title' => $request->title,
            'description' => $request->description,
            'condition' => $request->condition,
            'starting_bid' => $request->starting_bid,
            'reserve_price' => $request->filled('reserve_price') ? $request->reserve_price : null,
            'bid_increment' => $request->bid_increment,
            'duration_hours' => (int) $request->duration_hours,
            'rejection_reason' => null,
        ]);

        if ($request->hasFile('images')) {
            $existingCount = $listing->images()->count();
            $imageIndex = 0;
            foreach ($request->file('images') as $image) {
                if ($existingCount + $imageIndex >= 5) {
                    break;
                }
                $path = $image->store('auction_images/' . $listing->id, 'public');
                AuctionImage::create([
                    'auction_id' => $listing->id,
                    'image_path' => $path,
                    'is_primary' => $existingCount === 0 && $imageIndex === 0,
                ]);
                $imageIndex++;
            }
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
