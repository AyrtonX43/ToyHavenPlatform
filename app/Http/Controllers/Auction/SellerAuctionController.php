<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerAuctionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.seller-profile.index')
                ->with('error', 'You need an approved auction seller profile to list auctions.');
        }

        $profile = $user->auctionSellerProfile;
        $auctions = $profile?->auctions()->with('category')->orderByDesc('created_at')->paginate(10);

        return view('auctions.seller.index', compact('auctions'));
    }

    public function create()
    {
        $user = Auth::user();
        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.seller-profile.index')->with('error', 'Approved seller profile required.');
        }

        $profile = $user->auctionSellerProfile;
        $plan = $user->currentPlan();
        $maxActive = $plan?->getMaxActiveAuctions() ?? 0;
        $liveCount = $profile->auctions()->whereIn('status', ['live', 'approved', 'pending_approval'])->count();
        if ($maxActive > 0 && $liveCount >= $maxActive) {
            return redirect()->route('auctions.seller.index')
                ->with('error', "You have reached your limit of {$maxActive} active auctions.");
        }

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $plans = Plan::active()->ordered()->get();

        return view('auctions.seller.create', compact('categories', 'plans'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.seller-profile.index')->with('error', 'Approved seller profile required.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'starting_bid' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:0',
            'start_at' => 'required|date|after:now',
            'end_at' => 'required|date|after:start_at',
            'allowed_bidder_plan_ids' => 'nullable|array',
            'allowed_bidder_plan_ids.*' => 'exists:plans,id',
            'images' => 'nullable|array',
            'images.*' => 'image|max:5120',
        ]);

        $profile = $user->auctionSellerProfile;
        $validated['bid_increment'] = $validated['bid_increment'] ?? 1;
        $validated['allowed_bidder_plan_ids'] = ! empty($validated['allowed_bidder_plan_ids']) ? $validated['allowed_bidder_plan_ids'] : null;
        $validated['status'] = 'pending_approval';
        $validated['auction_seller_profile_id'] = $profile->id;

        $auction = Auction::create($validated);

        if ($request->hasFile('images')) {
            $order = 0;
            foreach ($request->file('images') as $file) {
                $path = $file->store('auctions/' . $auction->id, 'public');
                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'path' => $path,
                    'display_order' => $order++,
                    'is_primary' => $order === 1,
                ]);
            }
        }

        return redirect()->route('auctions.seller.index')
            ->with('success', 'Auction submitted for approval.');
    }

    public function edit(Auction $auction)
    {
        $user = Auth::user();
        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.seller-profile.index')->with('error', 'Approved seller profile required.');
        }

        $profile = $user->auctionSellerProfile;
        if ($auction->auction_seller_profile_id !== $profile->id) {
            abort(403);
        }
        if (! in_array($auction->status, ['draft', 'pending_approval'])) {
            return redirect()->route('auctions.seller.index')->with('error', 'Cannot edit this auction.');
        }

        $auction->load('images');
        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $plans = Plan::active()->ordered()->get();

        return view('auctions.seller.edit', compact('auction', 'categories', 'plans'));
    }

    public function update(Request $request, Auction $auction)
    {
        $user = Auth::user();
        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.seller-profile.index')->with('error', 'Approved seller profile required.');
        }

        $profile = $user->auctionSellerProfile;
        if ($auction->auction_seller_profile_id !== $profile->id) {
            abort(403);
        }
        if (! in_array($auction->status, ['draft', 'pending_approval'])) {
            return redirect()->route('auctions.seller.index')->with('error', 'Cannot edit this auction.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'starting_bid' => 'required|numeric|min:0',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'nullable|numeric|min:0',
            'start_at' => 'required|date',
            'end_at' => 'required|date|after:start_at',
            'allowed_bidder_plan_ids' => 'nullable|array',
            'allowed_bidder_plan_ids.*' => 'exists:plans,id',
        ]);

        $validated['allowed_bidder_plan_ids'] = $validated['allowed_bidder_plan_ids'] ?? null;
        $auction->update($validated);

        return redirect()->route('auctions.seller.index')->with('success', 'Auction updated.');
    }
}
