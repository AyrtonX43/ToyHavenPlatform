<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\Category;
use App\Models\Plan;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $canBid = auth()->check() && auth()->user()->hasActiveMembership();
        $hasMembership = $canBid;

        // Non-members see only membership plans (no auction listing, my bids, history)
        if (auth()->check() && ! $hasMembership) {
            $plans = Plan::where('is_active', true)->orderBy('sort_order')->orderBy('price')->get();

            return view('auctions.index', [
                'auctions' => collect(),
                'categories' => collect(),
                'canBid' => false,
                'showPlansOnly' => true,
                'plans' => $plans,
                'currentPlan' => null,
            ]);
        }

        $query = Auction::where('status', Auction::STATUS_ACTIVE)
            ->where('end_at', '>', now())
            ->with('images', 'category');

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        if ($request->filled('search')) {
            $term = $request->search;
            $query->where(function ($q) use ($term) {
                $q->where('title', 'like', "%{$term}%")
                    ->orWhere('description', 'like', "%{$term}%");
            });
        }

        $auctions = $query->orderByDesc('is_promoted')
            ->orderBy('end_at')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::where('is_active', true)->orderBy('name')->get();
        $currentPlan = auth()->user()?->currentPlan();

        return view('auctions.index', [
            'auctions' => $auctions,
            'categories' => $categories,
            'canBid' => $canBid,
            'showPlansOnly' => false,
            'plans' => collect(),
            'currentPlan' => $currentPlan,
        ]);
    }

    public function show(Auction $auction)
    {
        if ($auction->status !== Auction::STATUS_ACTIVE && $auction->status !== Auction::STATUS_ENDED) {
            abort(404);
        }

        $auction->increment('views_count');
        $auction->load(['images', 'category', 'bids' => fn ($q) => $q->orderByDesc('amount')->limit(20)]);

        $canBid = auth()->check()
            && auth()->user()->hasActiveMembership()
            && ! auth()->user()->isAuctionBanned()
            && ! auth()->user()->isAuctionSuspended()
            && $auction->canBid();

        return view('auctions.show', compact('auction', 'canBid'));
    }

    public function liveRoom(Auction $auction)
    {
        if ($auction->status !== Auction::STATUS_ACTIVE) {
            return redirect()->route('auctions.show', $auction);
        }

        $auction->load('images', 'bids');

        return view('auctions.live-room', compact('auction'));
    }

    public function myBids(Request $request)
    {
        $user = $request->user();
        $query = AuctionBid::where('user_id', $user->id)
            ->with('auction.images');

        $bids = $query->orderByDesc('created_at')->paginate(15);

        return view('auctions.my-bids', compact('bids'));
    }
}
