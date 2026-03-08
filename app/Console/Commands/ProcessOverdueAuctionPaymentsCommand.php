<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessOverdueAuctionPaymentsCommand extends Command
{
    protected $signature = 'auctions:process-overdue-payments';

    protected $description = 'Offer second chance to next bidders when winner does not pay';

    public function handle(): int
    {
        $overdue = AuctionPayment::where('status', 'pending')
            ->where('payment_deadline', '<', now())
            ->with('auction')
            ->get();

        foreach ($overdue as $payment) {
            $auction = $payment->auction;
            $nextBidder = AuctionBid::where('auction_id', $auction->id)
                ->where('user_id', '!=', $payment->winner_id)
                ->orderByDesc('amount')
                ->first();

            if (! $nextBidder) {
                $this->logOffense($payment, $auction, 'refunded_no_second_chance');
                $payment->update(['status' => 'refunded']);
                continue;
            }

            if (! Schema::hasTable('auction_second_chances')) {
                $this->logOffense($payment, $auction, 'refunded_no_second_chance');
                $payment->update(['status' => 'refunded']);
                continue;
            }

            $exists = DB::table('auction_second_chances')
                ->where('auction_id', $auction->id)
                ->where('user_id', $nextBidder->user_id)
                ->exists();

            if ($exists) {
                $this->logOffense($payment, $auction, 'second_chance_already_offered');
                $payment->update(['status' => 'refunded']);
                continue;
            }

            DB::table('auction_second_chances')->insert([
                'auction_id' => $auction->id,
                'user_id' => $nextBidder->user_id,
                'bid_amount' => $nextBidder->amount,
                'queue_position' => 2,
                'status' => 'offered',
                'payment_link' => route('auction.payment.show', $payment),
                'offered_at' => now(),
                'deadline' => now()->addHours(48),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->logOffense($payment, $auction, 'second_chance_offered');
            $payment->update(['status' => 'refunded']);

            $nextBidder->user->notify(new \App\Notifications\SecondChanceOfferNotification(
                $auction,
                (float) $nextBidder->amount,
                route('auction.payment.show', $payment)
            ));
        }

        $this->info('Processed ' . $overdue->count() . ' overdue payment(s).');

        return 0;
    }

    protected function logOffense(AuctionPayment $payment, Auction $auction, string $actionTaken): void
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
