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

    protected $description = 'Apply penalties for failed payments and offer second chance to next bidder';

    public function handle(): int
    {
        $overdue = AuctionPayment::where('payment_status', 'pending')
            ->where('payment_deadline', '<', now())
            ->with(['auction', 'winner'])
            ->get();

        foreach ($overdue as $payment) {
            try {
                DB::transaction(function () use ($payment) {
                    $winner = $payment->winner;
                    if ($winner) {
                        $count = ($winner->auction_suspension_offence_count ?? 0) + 1;

                        if ($count >= 3) {
                            $winner->update([
                                'auction_banned' => true,
                                'auction_suspension_offence_count' => $count,
                            ]);
                        } else {
                            $days = $count === 1 ? 5 : 30;
                            $winner->update([
                                'auction_suspended_until' => now()->addDays($days),
                                'auction_suspension_offence_count' => $count,
                            ]);
                        }
                    }

                    $payment->update(['payment_status' => 'failed']);
                    $this->offerSecondChance($payment);
                });
            } catch (\Exception $e) {
                Log::error('Failed to process overdue auction payment', ['payment_id' => $payment->id, 'error' => $e->getMessage()]);
            }
        }

        $expiredOffers = AuctionSecondChance::where('status', 'offered')
            ->where('deadline', '<', now())
            ->get();

        foreach ($expiredOffers as $offer) {
            try {
                DB::transaction(function () use ($offer) {
                    $offer->update(['status' => 'expired']);
                    $this->offerNextInQueue($offer->auction_id, $offer->queue_position);
                });
            } catch (\Exception $e) {
                Log::error('Failed to process expired second chance', ['offer_id' => $offer->id, 'error' => $e->getMessage()]);
            }
        }

        return self::SUCCESS;
    }

    private function offerSecondChance(AuctionPayment $payment): void
    {
        $auction = $payment->auction;

        $topBidders = AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', '!=', $payment->winner_id)
            ->selectRaw('user_id, MAX(amount) as max_bid')
            ->groupBy('user_id')
            ->orderByDesc('max_bid')
            ->limit(5)
            ->get();

        $position = 1;
        foreach ($topBidders as $bidder) {
            $user = User::find($bidder->user_id);
            if (! $user || $user->auction_banned || $user->isAuctionSuspended()) {
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
        }
    }
}
