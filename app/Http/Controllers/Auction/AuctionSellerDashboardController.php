<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\AuctionPayment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;

class AuctionSellerDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('auction.index')
                ->with('error', 'Membership required to access the auction seller dashboard.');
        }

        if (! $user->hasAnyApprovedAuctionSeller()) {
            return redirect()->route('auction.index')
                ->with('error', 'You must be an approved auction seller to access this dashboard.');
        }

        $sales = Collection::make();
        if (Schema::hasTable('auction_payments')) {
            $sales = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
            ->whereIn('status', ['paid', 'held', 'released'])
            ->with(['auction', 'winner'])
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();
        }

        return view('auction.seller.dashboard', compact('sales'));
    }
}
