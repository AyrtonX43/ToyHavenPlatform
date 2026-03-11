<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Notifications\PaymentOverdueNotification;
use App\Notifications\SecondChanceOfferNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ProcessOverdueAuctionPaymentsCommand extends Command
{
    protected $signature = 'auctions:process-overdue-payments';

    protected $description = 'Mark overdue auction payments as refunded, log offenses, and offer second-chance to runner-up';

    public function handle(): int
    {
        $overdue = AuctionPayment::where('status', 'pending')
            ->where('payment_deadline', '<', now())
            ->with(['auction', 'winner'])
            ->get();

        foreach ($overdue as $payment) {
            $auction = $payment->auction;

            $this->logOffense($payment, $auction, 'payment_overdue');
            $payment->update(['status' => 'refunded']);

            Log::info('Auction payment marked overdue', [
                'payment_id' => $payment->id,
                'auction_id' => $auction->id,
                'winner_id' => $payment->winner_id,
            ]);

            try {
                $payment->winner?->notify(new PaymentOverdueNotification($auction, $payment));
            } catch (\Exception $e) {
                Log::error('Failed to send overdue notification', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if (! $payment->is_second_chance) {
                $this->offerSecondChance($auction, $payment);
            }
        }

        $this->info('Processed ' . $overdue->count() . ' overdue payment(s).');

        return 0;
    }

    private function offerSecondChance(Auction $auction, AuctionPayment $overduePayment): void
    {
        $runnerUp = AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', '!=', $overduePayment->winner_id)
            ->orderByDesc('amount')
            ->first();

        if (! $runnerUp) {
            return;
        }

        if ($auction->reserve_price && (float) $runnerUp->amount < (float) $auction->reserve_price) {
            Log::info('Second-chance runner-up bid below reserve', [
                'auction_id' => $auction->id,
                'runner_up_bid' => $runnerUp->amount,
                'reserve' => $auction->reserve_price,
            ]);
            return;
        }

        $existingSecondChance = AuctionPayment::where('auction_id', $auction->id)
            ->where('winner_id', $runnerUp->user_id)
            ->where('is_second_chance', true)
            ->first();

        if ($existingSecondChance) {
            return;
        }

        $deadline = now()->addHours(48);

        $payment = AuctionPayment::create([
            'auction_id' => $auction->id,
            'winner_id' => $runnerUp->user_id,
            'amount' => $runnerUp->amount,
            'status' => 'pending',
            'payment_deadline' => $deadline,
            'is_second_chance' => true,
        ]);

        $auction->update([
            'winner_id' => $runnerUp->user_id,
            'winning_amount' => $runnerUp->amount,
        ]);

        try {
            $runnerUp->user->notify(new SecondChanceOfferNotification($auction, $payment));
        } catch (\Exception $e) {
            Log::error('Failed to notify second-chance winner', [
                'auction_id' => $auction->id,
                'user_id' => $runnerUp->user_id,
                'error' => $e->getMessage(),
            ]);
        }

        Log::info('Second-chance offer created', [
            'auction_id' => $auction->id,
            'runner_up_user_id' => $runnerUp->user_id,
            'amount' => $runnerUp->amount,
            'payment_id' => $payment->id,
        ]);
    }

    private function logOffense(AuctionPayment $payment, Auction $auction, string $actionTaken): void
    {
        if (! Schema::hasTable('auction_offense_logs') || ! $payment->winner_id) {
            return;
        }

        $count = DB::table('auction_offense_logs')
            ->where('user_id', $payment->winner_id)
            ->where('reason', 'payment_deadline_missed')
            ->count() + 1;

        DB::table('auction_offense_logs')->insert([
            'user_id' => $payment->winner_id,
            'auction_id' => $auction->id,
            'auction_payment_id' => $payment->id,
            'reason' => 'payment_deadline_missed',
            'occurrence' => $count,
            'action_taken' => $actionTaken,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
