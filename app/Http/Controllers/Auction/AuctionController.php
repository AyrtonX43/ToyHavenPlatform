<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuctionController extends Controller
{
    /**
     * List live auctions. Teaser for non-members (show but cannot bid)
     */
    public function index(Request $request)
    {
        $query = Auction::live()->with(['category', 'categories', 'bids' => fn ($q) => $q->orderByDesc('amount')->limit(1)]);

        if ($request->filled('category')) {
            $catId = $request->category;
            $query->where(function ($sql) use ($catId) {
                $sql->where('category_id', $catId)
                    ->orWhereHas('categories', fn ($q) => $q->where('categories.id', $catId));
            });
        }

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(fn ($sql) => $sql->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"));
        }

        $user = Auth::user();
        $auctions = $query->orderBy('end_at')->paginate(12);
        $hasMembership = $user?->hasActiveMembership() ?? false;

        $plans = $hasMembership ? collect() : Plan::active()->ordered()->get();

        $featuredAuctions = Auction::live()
            ->where('is_promoted', true)
            ->where('promoted_until', '>', now())
            ->with(['category'])
            ->orderByDesc('promoted_until')
            ->limit(6)
            ->get();

        $currentPlan = $user?->currentPlan();
        $isVipMember = $hasMembership && $currentPlan && strtolower($currentPlan->slug) === 'vip';

        return view('auctions.index', [
            'auctions' => $auctions,
            'hasMembership' => $hasMembership,
            'isVipMember' => $isVipMember,
            'plans' => $plans,
            'featuredAuctions' => $featuredAuctions,
        ]);
    }

    /**
     * Show auction detail
     */
    public function show(Auction $auction)
    {
        $user = Auth::user();
        $hasMembership = $user?->hasActiveMembership() ?? false;

        if (! $auction->isVisibleToUser($user)) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('info', 'This auction is for members only. Join membership to view and bid.');
        }

        // Increment view count
        $auction->increment('views_count');

        $auction->load(['bids' => fn ($q) => $q->with('user')->orderByDesc('amount')->limit(20), 'category', 'images']);

        return view('auctions.show', [
            'auction' => $auction,
            'hasMembership' => $hasMembership,
            'canBid' => $user && $auction->canUserBid($user),
        ]);
    }

    /**
     * Live auction room for live_event auctions
     */
    public function liveRoom(Auction $auction)
    {
        $user = Auth::user();
        $hasMembership = $user?->hasActiveMembership() ?? false;

        if (! $auction->isVisibleToUser($user)) {
            return redirect()->route('membership.index', ['intent' => 'auction'])
                ->with('info', 'This auction is for members only.');
        }

        if (! $auction->isLiveEvent()) {
            return redirect()->route('auctions.show', $auction);
        }

        $auction->increment('views_count');
        $auction->load(['bids' => fn ($q) => $q->with('user')->orderByDesc('amount')->limit(50), 'category', 'images']);

        return view('auctions.live-room', [
            'auction' => $auction,
            'hasMembership' => $hasMembership,
            'canBid' => $user && $auction->canUserBid($user),
        ]);
    }

    /**
     * User's bids and won auctions
     */
    public function myBids()
    {
        $user = Auth::user();

        $myBids = \App\Models\AuctionBid::where('user_id', $user->id)
            ->with(['auction' => fn ($q) => $q->with('category')])
            ->orderByDesc('created_at')
            ->paginate(15);

        $wonAuctions = Auction::where('winner_id', $user->id)
            ->with('category')
            ->orderByDesc('end_at')
            ->get();

        return view('auctions.my-bids', [
            'myBids' => $myBids,
            'wonAuctions' => $wonAuctions,
        ]);
    }
}
