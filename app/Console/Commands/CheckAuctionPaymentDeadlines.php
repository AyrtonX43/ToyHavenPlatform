<?php

namespace App\Console\Commands;

use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Models\AuctionSecondChance;
use App\Models\User;
use App\Notifications\AuctionSecondChanceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CheckAuctionPaymentDeadlines extends Command
{
    protected $signature = 'auction:check-payment-deadlines';

    protected $description = 'Ban winners who failed to pay within 24 hours and trigger second-chance queue';

    public function handle(): int
    {
        $overduePayments = AuctionPayment::where('payment_status', 'pending')
            ->where('payment_deadline', '<', now())
            ->with(['auction', 'winner'])
            ->get();

        foreach ($overduePayments as $payment) {
            try {
                DB::transaction(function () use ($payment) {
                    // Ban the joy bidder
                    $winner = $payment->winner;
                    if ($winner && ! $winner->is_banned) {
                        $winner->update([
                            'is_banned' => true,
                            'banned_at' => now(),
                            'ban_reason' => 'Failed to pay for won auction #' . $payment->auction_id . ' within 24 hours (joy bidding).',
                        ]);
                    }

                    $payment->update(['payment_status' => 'failed']);

                    // Trigger second-chance queue
                    $this->offerSecondChance($payment);
                });
            } catch (\Exception $e) {
                Log::error('Failed to process overdue auction payment', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // Also check second-chance offers that expired
        $expiredOffers = AuctionSecondChance::where('status', 'offered')
            ->where('deadline', '<', now())
            ->with('auction')
            ->get();

        foreach ($expiredOffers as $offer) {
            try {
                DB::transaction(function () use ($offer) {
                    $offer->update(['status' => 'expired']);
                    $this->offerNextInQueue($offer->auction_id, $offer->queue_position);
                });
            } catch (\Exception $e) {
                Log::error('Failed to process expired second chance', [
                    'offer_id' => $offer->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return self::SUCCESS;
    }

    private function offerSecondChance(AuctionPayment $payment): void
    {
        $auction = $payment->auction;

        $topBidders = AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', '!=', $payment->winner_id)
            ->select('user_id', DB::raw('MAX(amount) as max_bid'))
            ->groupBy('user_id')
            ->orderByDesc('max_bid')
            ->limit(5)
            ->get();

        $position = 1;
        foreach ($topBidders as $bidder) {
            $user = User::find($bidder->user_id);
            if (! $user || $user->is_banned) {
                continue;
            }

            AuctionSecondChance::updateOrCreate(
                ['auction_id' => $auction->id, 'user_id' => $bidder->user_id],
                [
                    'bid_amount' => $bidder->max_bid,
                    'queue_position' => $position,
                    'status' => $position === 1 ? 'offered' : 'waiting',
                    'offered_at' => $position === 1 ? now() : null,
                    'deadline' => $position === 1 ? now()->addHours(24) : null,
                ]
            );

            if ($position === 1) {
                try {
                    $user->notify(new AuctionSecondChanceNotification($auction, $bidder->max_bid));
                } catch (\Exception $e) {
                    Log::warning('Failed to send second chance notification', ['error' => $e->getMessage()]);
                }
            }

            $position++;
        }

        if ($topBidders->isEmpty()) {
            $auction->update(['status' => 'ended']);
        }
    }

    private function offerNextInQueue(int $auctionId, int $currentPosition): void
    {
        $next = AuctionSecondChance::where('auction_id', $auctionId)
            ->where('queue_position', '>', $currentPosition)
            ->where('status', 'waiting')
            ->orderBy('queue_position')
            ->first();

        if ($next) {
            $next->update([
                'status' => 'offered',
                'offered_at' => now(),
                'deadline' => now()->addHours(24),
            ]);

            $user = User::find($next->user_id);
            if ($user) {
                $auction = $next->auction;
                try {
                    $user->notify(new AuctionSecondChanceNotification($auction, $next->bid_amount));
                } catch (\Exception $e) {
                    Log::warning('Failed to send second chance notification', ['error' => $e->getMessage()]);
                }
            }
        } else {
            $auction = \App\Models\Auction::find($auctionId);
            if ($auction && $auction->status !== 'ended') {
                $auction->update(['winner_id' => null, 'winning_amount' => null]);
            }
        }
    }
}
