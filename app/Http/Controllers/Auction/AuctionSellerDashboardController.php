<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
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

        $isIndividualSeller = $user->hasApprovedIndividualAuctionSeller();
        $isBusinessSeller = $user->hasApprovedBusinessAuctionSeller();

        $businessVerification = null;
        if ($isBusinessSeller) {
            $businessVerification = $user->approvedAuctionSellerVerifications()
                ->where('type', 'business')
                ->first();
        }

        $sales = Collection::make();
        if (Schema::hasTable('auction_payments')) {
            $sales = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['pending', 'paid', 'held', 'released'])
                ->with(['auction', 'winner'])
                ->orderByDesc('created_at')
                ->limit(20)
                ->get();
        }

        $quickStats = [
            'active' => Auction::where('user_id', $user->id)->active()->count(),
            'pending_approval' => Auction::where('user_id', $user->id)->pendingApproval()->count(),
            'ended' => Auction::where('user_id', $user->id)->ended()->count(),
            'pending_shipment' => 0,
        ];

        if (Schema::hasTable('auction_payments')) {
            $quickStats['pending_shipment'] = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['paid', 'held'])
                ->where(function ($q) {
                    $q->whereNull('delivery_status')
                      ->orWhere('delivery_status', 'pending_shipment');
                })
                ->count();
        }

        return view('auction.seller.dashboard', compact(
            'sales', 'isIndividualSeller', 'isBusinessSeller',
            'businessVerification', 'quickStats'
        ));
    }

    public function stats()
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

        $stats = [
            'total_revenue' => 0,
            'items_sold' => 0,
            'pending_shipment' => 0,
            'active_listings' => 0,
        ];

        if (Schema::hasTable('auction_payments')) {
            $payments = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['paid', 'held', 'released'])
                ->get();

            $stats['total_revenue'] = $payments->sum('amount');
            $stats['items_sold'] = $payments->count();
            
            $stats['pending_shipment'] = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['paid', 'held'])
                ->where(function($q) {
                    $q->whereNull('delivery_status')
                      ->orWhere('delivery_status', 'pending_shipment');
                })
                ->count();
        }

        if (Schema::hasTable('auctions')) {
            $stats['active_listings'] = \App\Models\Auction::where('user_id', $user->id)->active()->count();
        }

        return view('auction.seller.stats', compact('stats'));
    }
}
