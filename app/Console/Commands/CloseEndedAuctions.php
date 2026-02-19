<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Notifications\AuctionOutbidNotification;
use App\Notifications\AuctionWonNotification;
use Illuminate\Console\Command;

class CloseEndedAuctions extends Command
{
    protected $signature = 'auction:close-ended';

    protected $description = 'Close auctions that have passed their end time and notify winner';

    public function handle(): int
    {
        $ended = Auction::where('status', 'live')
            ->where('end_at', '<=', now())
            ->get();

        foreach ($ended as $auction) {
            $winnerBid = $auction->bids()->orderByDesc('amount')->first();

            if ($winnerBid) {
                $auction->update([
                    'status' => 'ended',
                    'winner_id' => $winnerBid->user_id,
                    'winning_amount' => $winnerBid->amount,
                ]);

                $winnerBid->user->notify(new AuctionWonNotification($auction));

                // Notify outbid users (excluding winner)
                $outbidUserIds = $auction->bids()
                    ->where('user_id', '!=', $winnerBid->user_id)
                    ->distinct()
                    ->pluck('user_id');

                foreach ($outbidUserIds as $userId) {
                    $lastBid = $auction->bids()->where('user_id', $userId)->orderByDesc('amount')->first();
                    if ($lastBid) {
                        $user = $lastBid->user;
                        if ($user) {
                            $user->notify(new AuctionOutbidNotification($auction, $lastBid));
                        }
                    }
                }
            } else {
                $auction->update(['status' => 'ended']);
            }
        }

        $this->info("Closed {$ended->count()} auction(s).");

        return 0;
    }
}
