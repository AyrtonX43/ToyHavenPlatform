<?php

namespace App\Http\Controllers\Auction;

use App\Events\AuctionEnded;
use App\Events\UserOutbid;
use App\Events\UserWonAuction;
use App\Http\Controllers\Controller;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Notifications\AuctionWonNotification;
use App\Notifications\AuctionWonSellerNotification;
use Illuminate\Support\Facades\Log;
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

        $with = ['user', 'category', 'images'];
        if (Schema::hasTable('auction_bids')) {
            $with['bids'] = fn ($q) => $q->orderByDesc('amount')->limit(10);
        }
        $auction->load($with);
        if (! $auction->relationLoaded('bids')) {
            $auction->setRelation('bids', collect([]));
        }

        $winnerPayment = null;
        if ($auction->status === 'ended' && $auction->winner_id && Schema::hasTable('auction_payments')) {
            $winnerPayment = AuctionPayment::where('auction_id', $auction->id)
                ->where('winner_id', $auction->winner_id)
                ->whereIn('status', ['pending', 'paid', 'held', 'released'])
                ->latest()
                ->first();

            if (! $winnerPayment && $auction->winner_id === $user->id && $auction->meetsReserve()) {
                $winnerPayment = AuctionPayment::create([
                    'auction_id' => $auction->id,
                    'winner_id' => $auction->winner_id,
                    'amount' => $auction->winning_amount,
                    'status' => 'pending',
                    'payment_deadline' => now()->addHours(48),
                    'is_second_chance' => false,
                ]);
            }
        }
        $auction->setRelation('payment', $winnerPayment);

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

    /**
     * AJAX endpoint: check if auction should be ended (when client countdown reaches zero).
     * Ends the auction immediately instead of waiting for the cron job.
     */
    public function checkEnd(Auction $auction)
    {
        if (! request()->expectsJson()) {
            abort(403);
        }

        try {
            $user = auth()->user();

            if ($auction->status === 'ended') {
                $payment = null;
                if ($auction->winner_id && $auction->meetsReserve() && Schema::hasTable('auction_payments')) {
                    $payment = AuctionPayment::where('auction_id', $auction->id)
                        ->where('winner_id', $auction->winner_id)
                        ->whereIn('status', ['pending', 'paid', 'held', 'released'])
                        ->latest()
                        ->first();
                }

                return response()->json([
                    'ended' => true,
                    'outcome' => $auction->auction_outcome,
                    'winner_id' => $auction->winner_id,
                    'payment_link' => ($user && $auction->winner_id === $user->id && $payment)
                        ? route('auction.payment.show', $payment)
                        : null,
                ]);
            }

            if ($auction->status !== 'active' || ! $auction->end_at || $auction->end_at->isFuture()) {
                return response()->json([
                    'ended' => false,
                    'outcome' => null,
                    'winner_id' => null,
                    'payment_link' => null,
                ]);
            }

            $outcome = $auction->winner_id
                ? ($auction->meetsReserve() ? 'sold' : 'reserve_not_met')
                : 'no_bids';

            $auction->update(['status' => 'ended', 'auction_outcome' => $outcome]);

            try {
                broadcast(new AuctionEnded($auction));
            } catch (\Exception $e) {
                Log::error('Failed to broadcast AuctionEnded', [
                    'auction_id' => $auction->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $payment = null;
            if ($auction->winner_id && $auction->meetsReserve()) {
                $existing = AuctionPayment::where('auction_id', $auction->id)
                    ->where('winner_id', $auction->winner_id)
                    ->first();

                if (! $existing) {
                    $deadline = now()->addHours(48);
                    $payment = AuctionPayment::create([
                        'auction_id' => $auction->id,
                        'winner_id' => $auction->winner_id,
                        'amount' => $auction->winning_amount,
                        'status' => 'pending',
                        'payment_deadline' => $deadline,
                        'is_second_chance' => false,
                    ]);

                    try {
                        $auction->winner->notify(new AuctionWonNotification($auction, $payment));
                    } catch (\Exception $e) {
                        Log::warning('Failed to notify auction winner', ['auction_id' => $auction->id, 'error' => $e->getMessage()]);
                    }

                    try {
                        $auction->user?->notify(new AuctionWonSellerNotification($auction, $payment));
                    } catch (\Exception $e) {
                        Log::warning('Failed to notify auction seller', ['auction_id' => $auction->id, 'error' => $e->getMessage()]);
                    }

                    try {
                        broadcast(new UserWonAuction($auction->winner_id, $auction, $payment));
                    } catch (\Exception $e) {
                        Log::error('Failed to broadcast UserWonAuction', [
                            'auction_id' => $auction->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } else {
                    $payment = $existing;
                }
            }

            $this->notifyLosingBidders($auction);

            return response()->json([
                'ended' => true,
                'outcome' => $outcome,
                'winner_id' => $auction->winner_id,
                'payment_link' => ($user && $auction->winner_id === $user->id && $payment)
                    ? route('auction.payment.show', $payment)
                    : null,
            ]);
        } catch (\Throwable $e) {
            Log::error('checkEnd failed', [
                'auction_id' => $auction->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ended' => false,
                'outcome' => null,
                'winner_id' => null,
                'payment_link' => null,
                'error' => app()->hasDebugModeEnabled() ? $e->getMessage() : 'An error occurred.',
            ], 500);
        }
    }

    private function notifyLosingBidders(Auction $auction): void
    {
        if (! $auction->winner_id) {
            return;
        }

        $losingBidderIds = AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', '!=', $auction->winner_id)
            ->groupBy('user_id')
            ->pluck('user_id');

        foreach ($losingBidderIds as $userId) {
            try {
                broadcast(new UserOutbid($userId, $auction, (float) $auction->winning_amount));
            } catch (\Exception $e) {
                Log::error('Failed to broadcast auction ended to losing bidder', [
                    'auction_id' => $auction->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
