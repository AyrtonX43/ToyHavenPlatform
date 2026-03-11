<?php

namespace App\Console\Commands;

use App\Models\AuctionPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class AutoConfirmAuctionDeliveryCommand extends Command
{
    protected $signature = 'auctions:auto-confirm-delivery';

    protected $description = 'Auto-confirm delivery for shipped auction payments after 7 days if buyer has not acted';

    public function handle(): int
    {
        $payments = AuctionPayment::where('delivery_status', 'shipped')
            ->whereNotNull('shipped_at')
            ->where('shipped_at', '<=', now()->subDays(7))
            ->whereNull('confirmed_at')
            ->where(function ($q) {
                $q->whereNull('dispute_status')
                  ->orWhere('dispute_status', '!=', 'open');
            })
            ->get();

        $confirmed = 0;

        foreach ($payments as $payment) {
            try {
                $payment->update([
                    'delivery_status' => 'delivered',
                    'confirmed_at' => now(),
                ]);
                $confirmed++;

                Log::info('Auto-confirmed auction delivery', [
                    'payment_id' => $payment->id,
                    'auction_id' => $payment->auction_id,
                    'shipped_at' => $payment->shipped_at,
                ]);
            } catch (\Exception $e) {
                Log::error('Failed to auto-confirm delivery', [
                    'payment_id' => $payment->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("Auto-confirmed delivery for {$confirmed} payment(s).");

        return 0;
    }
}
