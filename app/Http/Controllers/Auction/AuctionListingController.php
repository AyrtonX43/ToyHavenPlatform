<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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
            'starting_bid' => 'required|numeric|min:1',
            'bid_increment' => 'required|numeric|min:1',
            'duration_hours' => 'required|integer|min:1|max:720',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $verification = $user->approvedAuctionSellerVerifications()->first();
        $sellerType = $verification ? $verification->type : 'individual';

        $durationHours = (int) $request->duration_hours;

        Auction::create([
            'user_id' => $user->id,
            'seller_id' => null,
            'seller_type' => $sellerType,
            'product_id' => null,
            'user_product_id' => null,
            'category_id' => $request->category_id ?: null,
            'title' => $request->title,
            'description' => $request->description,
            'starting_bid' => $request->starting_bid,
            'bid_increment' => $request->bid_increment,
            'duration_hours' => $durationHours,
            'start_at' => null,
            'end_at' => null,
            'status' => 'draft',
        ]);

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

        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:5000',
            'starting_bid' => 'required|numeric|min:1',
            'bid_increment' => 'required|numeric|min:1',
            'duration_hours' => 'required|integer|min:1|max:720',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        $listing->update([
            'category_id' => $request->category_id ?: null,
            'title' => $request->title,
            'description' => $request->description,
            'starting_bid' => $request->starting_bid,
            'bid_increment' => $request->bid_increment,
            'duration_hours' => (int) $request->duration_hours,
            'rejection_reason' => null,
        ]);

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
