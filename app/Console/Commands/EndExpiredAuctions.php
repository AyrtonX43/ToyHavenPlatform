<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionPayment;
use App\Notifications\AuctionWonNotification;
use Illuminate\Console\Command;

class EndExpiredAuctions extends Command
{
    protected $signature = 'auction:end-expired';
    protected $description = 'End auctions past end_at and create payment records for winners';

    public function handle(): int
    {
        $ended = Auction::where('status', Auction::STATUS_ACTIVE)
            ->where('end_at', '<=', now())
            ->get();

        foreach ($ended as $auction) {
            $winningBid = $auction->currentWinningBid();
            if (! $winningBid) {
                $auction->update(['status' => Auction::STATUS_ENDED]);
                continue;
            }

            $auction->update([
                'status' => Auction::STATUS_ENDED,
                'winner_id' => $winningBid->user_id,
                'winning_amount' => $winningBid->amount,
            ]);

            AuctionPayment::create([
                'auction_id' => $auction->id,
                'winner_id' => $winningBid->user_id,
                'amount' => $winningBid->amount,
                'status' => AuctionPayment::STATUS_PENDING,
                'payment_deadline' => now()->addHours(24),
            ]);

            $winningBid->user->notify(new AuctionWonNotification($auction));
        }

        $this->info("Ended {$ended->count()} auction(s).");

        return 0;
    }
}
