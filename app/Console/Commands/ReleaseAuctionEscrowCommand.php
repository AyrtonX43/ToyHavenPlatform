<?php

namespace App\Console\Commands;

use App\Models\AuctionPayment;
use App\Notifications\EscrowReleasedNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseAuctionEscrowCommand extends Command
{
    protected $signature = 'auctions:release-escrow';

    protected $description = 'Auto-release escrow for delivered auction payments after the holding period';

    public function handle(): int
    {
        $payments = AuctionPayment::where('status', 'held')
            ->where('delivery_status', 'delivered')
            ->whereNotNull('confirmed_at')
            ->where('confirmed_at', '<=', now()->subDays(3))
            ->where(function ($q) {
                $q->whereNull('dispute_status')
                  ->orWhere('dispute_status', '!=', 'open');
            })
            ->with('auction')
            ->get();

        $released = 0;

        foreach ($payments as $payment) {
            try {
                $payment->update([
                    'status' => 'released',
                    'released_at' => now(),
                ]);
                $released++;

                Log::info('Auction escrow released', [
                    'payment_id' => $payment->id,
                    'auction_id' => $payment->auction_id,
                    'amount' => $payment->amount,
                ]);

                try {
                    $payment->auction->user?->notify(new EscrowReleasedNotification($payment->auction, $payment));
                } catch (\Exception $e) {
                    Log::error('Failed to send escrow released notification', [
                        'payment_id' => $payment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            } catch (\Exception $e) {
                Log::error('Failed to release escrow', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Released escrow for {$released} payment(s).");

        return 0;
    }
}
