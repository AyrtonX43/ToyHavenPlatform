<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ProcessOverdueAuctionPaymentsCommand extends Command
{
    protected $signature = 'auctions:process-overdue-payments';

    protected $description = 'Mark overdue auction payments as refunded and log offenses';

    public function handle(): int
    {
        $overdue = AuctionPayment::where('status', 'pending')
            ->where('payment_deadline', '<', now())
            ->with('auction')
            ->get();

        foreach ($overdue as $payment) {
            $auction = $payment->auction;
            $this->logOffense($payment, $auction, 'refunded_no_second_chance');
            $payment->update(['status' => 'refunded']);
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
