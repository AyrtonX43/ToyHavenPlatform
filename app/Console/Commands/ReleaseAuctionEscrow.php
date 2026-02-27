<?php

namespace App\Console\Commands;

use App\Models\AuctionPayment;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ReleaseAuctionEscrow extends Command
{
    protected $signature = 'auction:release-escrow';

    protected $description = 'Auto-release escrow for deliveries confirmed or delivered 3+ days ago';

    public function handle(): int
    {
        // Release for buyer-confirmed orders
        $confirmed = AuctionPayment::where('escrow_status', 'held')
            ->whereNotNull('confirmed_at')
            ->whereNull('released_at')
            ->get();

        $count = 0;

        foreach ($confirmed as $payment) {
            $payment->update([
                'escrow_status' => 'released',
                'released_at' => now(),
            ]);
            $count++;
        }

        // Auto-release for delivered + 3 days with no buyer confirmation
        $autoRelease = AuctionPayment::where('escrow_status', 'held')
            ->where('delivery_status', 'delivered')
            ->whereNull('confirmed_at')
            ->whereNull('released_at')
            ->where('delivered_at', '<=', now()->subDays(3))
            ->get();

        foreach ($autoRelease as $payment) {
            $payment->update([
                'escrow_status' => 'released',
                'released_at' => now(),
                'delivery_status' => 'confirmed',
                'confirmed_at' => now(),
            ]);
            $count++;
        }

        if ($count > 0) {
            $this->info("Released escrow for {$count} payment(s).");
            Log::info("Auction escrow auto-released for {$count} payments");
        }

        return self::SUCCESS;
    }
}
