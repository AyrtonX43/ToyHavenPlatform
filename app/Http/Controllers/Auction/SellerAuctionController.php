<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionImage;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SellerAuctionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.verification.index')
                ->with('info', 'Complete verification to list auctions.');
        }

        $auctions = Auction::where('user_id', $user->id)
            ->with(['category', 'images'])
            ->orderByDesc('created_at')
            ->paginate(15);

        return view('auctions.seller.index', compact('auctions'));
    }

    public function create()
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.verification.index')
                ->with('error', 'You must complete auction seller verification first.');
        }

        $plan = $user->currentPlan();
        $maxActive = $plan ? $plan->getMaxActiveAuctions() : 0;
        $activeCount = Auction::where('user_id', $user->id)
            ->whereIn('status', ['live', 'draft', 'pending_approval'])
            ->count();

        if ($maxActive > 0 && $activeCount >= $maxActive) {
            return redirect()->route('auctions.seller.index')
                ->with('error', "You have reached your limit of {$maxActive} active auctions.");
        }

        $categories = Category::orderBy('name')->get();

        return view('auctions.seller.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if (! $user->canListAuctions()) {
            return redirect()->route('auctions.verification.index');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'box_condition' => 'required|in:sealed,opened_complete,opened_incomplete,no_box',
            'authenticity_marks' => 'nullable|string|max:2000',
            'known_defects' => 'nullable|string|max:2000',
            'provenance' => 'nullable|string|max:2000',
            'starting_bid' => 'required|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'auction_type' => 'required|in:timed,live_event',
            'start_at' => 'required|date|after_or_equal:now',
            'end_at' => 'required_if:auction_type,timed|nullable|date|after:start_at',
            'duration_minutes' => 'required_if:auction_type,live_event|nullable|integer|min:5|max:480',
            'allowed_bidder_plans' => 'nullable|array',
            'allowed_bidder_plans.*' => 'in:basic,pro,vip,all',
            'images' => 'required|array|min:1|max:20',
            'images.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
            'photo_360' => 'nullable|array|max:36',
            'photo_360.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
            'verification_video' => 'nullable|file|mimes:mp4,webm|max:51200',
        ]);

        $categoryIds = $validated['category_ids'];

        $startAt = \Carbon\Carbon::parse($validated['start_at']);
        $maxStart = now()->addDays(5)->endOfDay();
        if ($startAt->gt($maxStart)) {
            return back()->withErrors(['start_at' => 'Start date must be within 5 days from now.'])->withInput();
        }

        $endAt = null;
        if ($validated['auction_type'] === 'timed' && ! empty($validated['end_at'])) {
            $endAt = \Carbon\Carbon::parse($validated['end_at']);
            $minEnd = $startAt->copy()->addDay();
            $maxEnd = $startAt->copy()->addDays(2);
            if ($endAt->lt($minEnd) || $endAt->gt($maxEnd)) {
                return back()->withErrors(['end_at' => 'End date must be 1–2 days after start date.'])->withInput();
            }
        }

        $allowedPlans = $request->input('allowed_bidder_plans', ['all']);
        if (empty($allowedPlans)) {
            $allowedPlans = ['all'];
        }

        $auction = Auction::create([
            'user_id' => $user->id,
            'seller_id' => $user->seller?->id,
            'category_id' => $categoryIds[0],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'box_condition' => $validated['box_condition'],
            'authenticity_marks' => $validated['authenticity_marks'] ?? null,
            'known_defects' => $validated['known_defects'] ?? null,
            'provenance' => $validated['provenance'] ?? null,
            'starting_bid' => $validated['starting_bid'],
            'reserve_price' => $validated['reserve_price'] ?? null,
            'bid_increment' => $validated['bid_increment'],
            'auction_type' => $validated['auction_type'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'allowed_bidder_plans' => $allowedPlans,
            'status' => 'pending_approval',
        ]);

        $auction->categories()->sync($categoryIds);

        $storagePath = 'auction_images/' . $auction->id;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $i => $image) {
                $path = $image->store($storagePath, 'public');
                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'path' => $path,
                    'image_type' => 'standard',
                    'display_order' => $i,
                ]);
            }
        }

        if ($request->hasFile('photo_360')) {
            foreach ($request->file('photo_360') as $i => $image) {
                $path = $image->store($storagePath . '/360', 'public');
                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'path' => $path,
                    'image_type' => 'photo_360',
                    'display_order' => $i,
                ]);
            }
        }

        if ($request->hasFile('verification_video')) {
            $videoPath = $request->file('verification_video')->store('auction_videos/' . $auction->id, 'public');
            $auction->update(['verification_video_path' => $videoPath]);
        }

        return redirect()->route('auctions.seller.index')
            ->with('success', 'Auction listing submitted for approval!');
    }

    public function edit(Auction $auction)
    {
        $user = Auth::user();

        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if (! in_array($auction->status, ['draft', 'pending_approval', 'cancelled'])) {
            return redirect()->route('auctions.seller.index')
                ->with('error', 'You can only edit draft or pending auctions.');
        }

        $categories = Category::orderBy('name')->get();
        $auction->load(['images', 'categories']);

        return view('auctions.seller.edit', compact('auction', 'categories'));
    }

    public function update(Request $request, Auction $auction)
    {
        $user = Auth::user();

        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if (! in_array($auction->status, ['draft', 'pending_approval', 'cancelled'])) {
            return redirect()->route('auctions.seller.index')
                ->with('error', 'You can only edit draft or pending auctions.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:5000',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'box_condition' => 'required|in:sealed,opened_complete,opened_incomplete,no_box',
            'authenticity_marks' => 'nullable|string|max:2000',
            'known_defects' => 'nullable|string|max:2000',
            'provenance' => 'nullable|string|max:2000',
            'starting_bid' => 'required|numeric|min:1',
            'reserve_price' => 'nullable|numeric|min:0',
            'bid_increment' => 'required|numeric|min:1',
            'auction_type' => 'required|in:timed,live_event',
            'start_at' => 'required|date|after_or_equal:now',
            'end_at' => 'required_if:auction_type,timed|nullable|date|after:start_at',
            'duration_minutes' => 'required_if:auction_type,live_event|nullable|integer|min:5|max:480',
            'allowed_bidder_plans' => 'nullable|array',
            'allowed_bidder_plans.*' => 'in:basic,pro,vip,all',
            'new_images' => 'nullable|array|max:20',
            'new_images.*' => 'image|mimes:jpg,jpeg,png,webp|max:10240',
            'verification_video' => 'nullable|file|mimes:mp4,webm|max:51200',
        ]);

        $categoryIds = $validated['category_ids'];

        $startAt = \Carbon\Carbon::parse($validated['start_at']);
        $maxStart = now()->addDays(5)->endOfDay();
        if ($startAt->gt($maxStart)) {
            return back()->withErrors(['start_at' => 'Start date must be within 5 days from now.'])->withInput();
        }

        $endAt = null;
        if ($validated['auction_type'] === 'timed' && ! empty($validated['end_at'])) {
            $endAt = \Carbon\Carbon::parse($validated['end_at']);
            $minEnd = $startAt->copy()->addDay();
            $maxEnd = $startAt->copy()->addDays(2);
            if ($endAt->lt($minEnd) || $endAt->gt($maxEnd)) {
                return back()->withErrors(['end_at' => 'End date must be 1–2 days after start date.'])->withInput();
            }
        }

        $allowedPlans = $request->input('allowed_bidder_plans', ['all']);
        if (empty($allowedPlans)) {
            $allowedPlans = ['all'];
        }

        $auction->update([
            'category_id' => $categoryIds[0],
            'title' => $validated['title'],
            'description' => $validated['description'],
            'box_condition' => $validated['box_condition'],
            'authenticity_marks' => $validated['authenticity_marks'] ?? null,
            'known_defects' => $validated['known_defects'] ?? null,
            'provenance' => $validated['provenance'] ?? null,
            'starting_bid' => $validated['starting_bid'],
            'reserve_price' => $validated['reserve_price'] ?? null,
            'bid_increment' => $validated['bid_increment'],
            'auction_type' => $validated['auction_type'],
            'start_at' => $startAt,
            'end_at' => $endAt,
            'duration_minutes' => $validated['duration_minutes'] ?? null,
            'allowed_bidder_plans' => $allowedPlans,
            'status' => 'pending_approval',
        ]);

        $auction->categories()->sync($categoryIds);

        if ($request->hasFile('new_images')) {
            $storagePath = 'auction_images/' . $auction->id;
            $maxOrder = $auction->images()->max('display_order') ?? -1;
            foreach ($request->file('new_images') as $image) {
                $maxOrder++;
                $path = $image->store($storagePath, 'public');
                AuctionImage::create([
                    'auction_id' => $auction->id,
                    'path' => $path,
                    'image_type' => 'standard',
                    'display_order' => $maxOrder,
                ]);
            }
        }

        if ($request->hasFile('verification_video')) {
            if ($auction->verification_video_path) {
                Storage::disk('public')->delete($auction->verification_video_path);
            }
            $videoPath = $request->file('verification_video')->store('auction_videos/' . $auction->id, 'public');
            $auction->update(['verification_video_path' => $videoPath]);
        }

        return redirect()->route('auctions.seller.index')
            ->with('success', 'Auction listing updated and resubmitted for approval.');
    }

    public function deleteImage(Auction $auction, AuctionImage $image)
    {
        $user = Auth::user();
        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }
        if ($image->auction_id !== $auction->id) {
            abort(404);
        }

        $standardCount = $auction->images()->where('image_type', 'standard')->count();
        if ($image->image_type === 'standard' && $standardCount <= 1) {
            return back()->with('error', 'You must keep at least one photo.');
        }

        Storage::disk('public')->delete($image->path);

        $wasPrimary = $image->display_order === 0;
        $image->delete();

        if ($wasPrimary) {
            $next = $auction->images()->where('image_type', 'standard')->orderBy('display_order')->first();
            if ($next) {
                $next->update(['display_order' => 0]);
            }
        }

        return back()->with('success', 'Image deleted.');
    }

    public function setPrimaryImage(Auction $auction, AuctionImage $image)
    {
        $user = Auth::user();
        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }
        if ($image->auction_id !== $auction->id || $image->image_type !== 'standard') {
            abort(404);
        }

        $auction->images()
            ->where('image_type', 'standard')
            ->where('display_order', 0)
            ->update(['display_order' => $image->display_order ?: 999]);

        $image->update(['display_order' => 0]);

        return back()->with('success', 'Primary image updated.');
    }

    public function deleteVideo(Auction $auction)
    {
        $user = Auth::user();
        if ($auction->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        if ($auction->verification_video_path) {
            Storage::disk('public')->delete($auction->verification_video_path);
            $auction->update(['verification_video_path' => null]);
        }

        return back()->with('success', 'Video deleted.');
    }
}
