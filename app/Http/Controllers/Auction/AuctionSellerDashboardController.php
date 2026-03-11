<?php

namespace App\Http\Controllers\Auction;

use App\Events\AuctionEnded;
use App\Events\UserWonAuction;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Notifications\AuctionWonNotification;
use App\Notifications\AuctionWonSellerNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

        $this->endOverdueAuctions($user->id);

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
                ->whereIn('status', ['pending', 'paid', 'held', 'released', 'refunded'])
                ->with(['auction.images', 'winner'])
                ->orderByDesc('created_at')
                ->limit(30)
                ->get();
        }

        $endedAuctions = Auction::where('user_id', $user->id)
            ->ended()
            ->with(['images', 'winner', 'payment'])
            ->orderByDesc('end_at')
            ->limit(20)
            ->get();

        $quickStats = [
            'active' => Auction::where('user_id', $user->id)->active()->count(),
            'pending_approval' => Auction::where('user_id', $user->id)->pendingApproval()->count(),
            'ended' => Auction::where('user_id', $user->id)->ended()->count(),
            'pending_shipment' => 0,
            'awaiting_payment' => 0,
            'total_sold' => 0,
        ];

        if (Schema::hasTable('auction_payments')) {
            $quickStats['pending_shipment'] = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['paid', 'held'])
                ->where(function ($q) {
                    $q->whereNull('delivery_status')
                      ->orWhere('delivery_status', 'pending_shipment');
                })
                ->count();

            $quickStats['awaiting_payment'] = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->where('status', 'pending')
                ->count();

            $quickStats['total_sold'] = AuctionPayment::whereHas('auction', fn ($q) => $q->where('user_id', $user->id))
                ->whereIn('status', ['paid', 'held', 'released'])
                ->count();
        }

        return view('auction.seller.dashboard', compact(
            'sales', 'endedAuctions', 'isIndividualSeller', 'isBusinessSeller',
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

    private function endOverdueAuctions(int $sellerId): void
    {
        try {
            $overdue = Auction::where('user_id', $sellerId)
                ->where('status', 'active')
                ->whereNotNull('end_at')
                ->where('end_at', '<=', now())
                ->get();

            foreach ($overdue as $auction) {
                $outcome = $auction->winner_id
                    ? ($auction->meetsReserve() ? 'sold' : 'reserve_not_met')
                    : 'no_bids';

                $auction->update(['status' => 'ended', 'auction_outcome' => $outcome]);

                try { broadcast(new AuctionEnded($auction)); } catch (\Throwable $e) { Log::error('Dashboard AuctionEnded broadcast failed', ['id' => $auction->id, 'error' => $e->getMessage()]); }

                if ($auction->winner_id && $auction->meetsReserve()) {
                    $exists = AuctionPayment::where('auction_id', $auction->id)
                        ->where('winner_id', $auction->winner_id)
                        ->exists();

                    if (! $exists) {
                        $payment = AuctionPayment::create([
                            'auction_id' => $auction->id,
                            'winner_id' => $auction->winner_id,
                            'amount' => $auction->winning_amount,
                            'status' => 'pending',
                            'payment_deadline' => now()->addHours(48),
                            'is_second_chance' => false,
                        ]);

                        try { $auction->winner?->notify(new AuctionWonNotification($auction, $payment)); } catch (\Throwable $e) {}
                        try { $auction->user?->notify(new AuctionWonSellerNotification($auction, $payment)); } catch (\Throwable $e) {}
                        try { broadcast(new UserWonAuction($auction->winner_id, $auction, $payment)); } catch (\Throwable $e) {}
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::error('endOverdueAuctions failed', ['seller_id' => $sellerId, 'error' => $e->getMessage()]);
        }
    }
}
