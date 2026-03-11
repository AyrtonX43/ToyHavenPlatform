<?php

namespace App\Http\Controllers\Auction;

use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Support\Facades\Schema;

class AuctionController extends Controller
{
    /**
     * Auction hub for members: active listings, shortcuts, etc.
     */
    public function index()
    {
        $user = auth()->user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership. Please subscribe to a plan to continue.');
        }

        $activeListings = Auction::with(['user', 'category', 'images'])
            ->active()
            ->orderByDesc('end_at')
            ->paginate(12);

        $pendingListings = Auction::with(['user', 'category', 'images'])
            ->pendingApproval()
            ->orderByDesc('updated_at')
            ->limit(6)
            ->get();

        return view('auction.index', compact('activeListings', 'pendingListings'));
    }

    /**
     * Show a single auction listing (public detail + bid form).
     */
    public function show(Auction $auction)
    {
        $user = auth()->user();

        if (! $user || ! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership.');
        }

        $canView = in_array($auction->status, ['active', 'ended', 'pending_approval']);
        if (! $canView) {
            return redirect()->route('auction.index')
                ->with('error', 'This auction is not available for viewing.');
        }

        $with = ['user', 'category', 'images', 'payment'];
        if (Schema::hasTable('auction_bids')) {
            $with['bids'] = fn ($q) => $q->orderByDesc('amount')->limit(10);
        }
        $auction->load($with);
        if (! $auction->relationLoaded('bids')) {
            $auction->setRelation('bids', collect([]));
        }

        // Ensure payment exists for winner when auction ended (e.g. if scheduler didn't run)
        if ($auction->status === 'ended'
            && $auction->winner_id === $user->id
            && $auction->meetsReserve()
            && Schema::hasTable('auction_payments')
            && ! $auction->payment) {
            $payment = AuctionPayment::create([
                'auction_id' => $auction->id,
                'winner_id' => $auction->winner_id,
                'amount' => $auction->winning_amount,
                'status' => 'pending',
                'payment_deadline' => now()->addHours(48),
                'is_second_chance' => false,
            ]);
            $auction->setRelation('payment', $payment);
        }

        $isSaved = Schema::hasTable('saved_auctions') && \Illuminate\Support\Facades\DB::table('saved_auctions')
            ->where('user_id', $user->id)
            ->where('auction_id', $auction->id)
            ->exists();

        return view('auction.show', compact('auction', 'isSaved'));
    }

    public function history()
    {
        $user = auth()->user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership.');
        }

        $payments = \App\Models\AuctionPayment::with(['auction.images'])
            ->where('winner_id', $user->id)
            ->orderByDesc('created_at')
            ->paginate(12);

        return view('auction.history', compact('payments'));
    }

    public function bids()
    {
        $user = auth()->user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership.');
        }

        $auctions = Auction::whereHas('bids', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['images', 'bids' => function ($q) use ($user) {
                $q->where('user_id', $user->id)->orderByDesc('amount');
            }])
            ->orderByDesc('end_at')
            ->paginate(12);

        return view('auction.bids', compact('auctions'));
    }

    public function saved()
    {
        $user = auth()->user();

        if (! $user->hasActiveMembership()) {
            return redirect()->route('membership.index')
                ->with('info', 'Auction access requires an active membership.');
        }

        $savedAuctions = Auction::whereHas('savedBy', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with(['images'])
            ->orderByDesc('end_at')
            ->paginate(12);

        return view('auction.saved', compact('savedAuctions'));
    }
}
