<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use Illuminate\Http\Request;

class AuctionController extends Controller
{
    public function index(Request $request)
    {
        $query = Auction::live()->with(['category', 'images']);

        $auctions = $query->orderBy('end_at')->paginate(12);

        return view('auctions.index', compact('auctions'));
    }

    public function show(Auction $auction)
    {
        $user = auth()->user();

        if ($auction->status !== 'live') {
            return redirect()->route('auctions.index')->with('error', 'This auction is not live.');
        }

        $auction->increment('views_count');
        $auction->load(['images', 'category', 'auctionSellerProfile']);

        $canBid = $user
            && $user->hasActiveMembership()
            && ! $user->isAuctionSuspended()
            && $this->isUserPlanAllowed($auction, $user);

        $bids = $auction->bids()
            ->orderByDesc('amount')
            ->limit(20)
            ->get()
            ->map(fn ($b) => [
                'anonymous_display_id' => $b->anonymous_display_id ?? 'Bidder #----',
                'amount' => $b->amount,
                'created_at' => $b->created_at,
            ]);

        return view('auctions.show', compact('auction', 'canBid', 'bids'));
    }

    private function isUserPlanAllowed(Auction $auction, $user): bool
    {
        $planIds = $auction->allowed_bidder_plan_ids;
        if ($planIds === null || $planIds === []) {
            return true;
        }
        $plan = $user->currentPlan();
        return $plan && in_array($plan->id, $planIds);
    }
}
