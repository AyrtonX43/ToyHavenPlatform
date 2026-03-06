<?php

namespace App\Console\Commands;

use App\Models\AuctionBid;
use App\Models\AuctionOffense;
use App\Models\AuctionOffenseLog;
use App\Models\AuctionPayment;
use App\Notifications\AuctionSecondChanceNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckAuctionPaymentDeadlines extends Command
{
    protected $signature = 'auction:check-payment-deadlines';
    protected $description = 'Handle missed payment deadlines: transfer to 2nd bidder, apply offense suspension';

    public function handle(): int
    {
        $missed = AuctionPayment::where('status', AuctionPayment::STATUS_PENDING)
            ->whereNotNull('payment_deadline')
            ->where('payment_deadline', '<', now())
            ->get();

        foreach ($missed as $payment) {
            $auction = $payment->auction;
            $originalWinner = $payment->winner;

            $secondBid = $auction->bids()
                ->where('user_id', '!=', $originalWinner->id)
                ->orderByDesc('amount')
                ->first();

            if ($secondBid) {
                $payment->update([
                    'winner_id' => $secondBid->user_id,
                    'amount' => $secondBid->amount,
                    'payment_deadline' => now()->addHours(24),
                    'is_second_chance' => true,
                ]);

                $auction->update([
                    'winner_id' => $secondBid->user_id,
                    'winning_amount' => $secondBid->amount,
                ]);

                $auction->bids()->where('is_winning', true)->update(['is_winning' => false]);
                $secondBid->update(['is_winning' => true]);

                $secondBid->user->notify(new AuctionSecondChanceNotification($auction));
            } else {
                $payment->update(['status' => AuctionPayment::STATUS_REFUNDED]);
                $auction->update(['winner_id' => null, 'winning_amount' => null]);
            }

            $occurrence = $originalWinner->auctionOffenseCount() + 1;

            DB::transaction(function () use ($originalWinner, $auction, $payment, $occurrence) {
                AuctionOffense::create([
                    'user_id' => $originalWinner->id,
                    'offense_type' => 'payment_deadline_missed',
                    'occurrence' => $occurrence,
                    'suspended_until' => match ($occurrence) {
                        1 => now()->addDays(7),
                        2 => now()->addDays(60),
                        default => null,
                    },
                ]);

                AuctionOffenseLog::create([
                    'user_id' => $originalWinner->id,
                    'auction_id' => $auction->id,
                    'auction_payment_id' => $payment->id,
                    'reason' => 'Payment deadline missed',
                    'occurrence' => $occurrence,
                    'action_taken' => match ($occurrence) {
                        1 => '7 day suspension',
                        2 => '60 day suspension',
                        default => 'Permanent ban',
                    },
                ]);

                if ($occurrence >= 3) {
                    $originalWinner->update(['auction_banned_at' => now()]);
                } else {
                    $originalWinner->update([
                        'auction_suspended_until' => match ($occurrence) {
                            1 => now()->addDays(7),
                            2 => now()->addDays(60),
                            default => null,
                        },
                    ]);
                }
            });
        }

        $this->info("Processed {$missed->count()} missed payment(s).");

        return 0;
    }
}
