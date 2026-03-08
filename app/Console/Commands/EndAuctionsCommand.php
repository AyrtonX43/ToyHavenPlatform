<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionPayment;
use App\Notifications\AuctionWonNotification;
use Illuminate\Console\Command;

class EndAuctionsCommand extends Command
{
    protected $signature = 'auctions:end';

    protected $description = 'Mark ended auctions and create payment records for winners';

    public function handle(): int
    {
        $ended = Auction::active()
            ->where('end_at', '<=', now())
            ->get();

        foreach ($ended as $auction) {
            $outcome = $auction->winner_id ? ($auction->meetsReserve() ? 'sold' : 'reserve_not_met') : 'no_bids';
            $auction->update(['status' => 'ended', 'auction_outcome' => $outcome]);

            if ($auction->winner_id && $auction->meetsReserve()) {
                $existing = AuctionPayment::where('auction_id', $auction->id)->where('winner_id', $auction->winner_id)->first();
                if (! $existing) {
                    $deadline = now()->addHours(48);
                    $payment = AuctionPayment::create([
                        'auction_id' => $auction->id,
                        'winner_id' => $auction->winner_id,
                        'amount' => $auction->winning_amount,
                        'status' => 'pending',
                        'payment_deadline' => $deadline,
                        'is_second_chance' => false,
                    ]);

                    try {
                        $auction->winner->notify(new AuctionWonNotification($auction, $payment));
                    } catch (\Exception $e) {
                        $this->error('Failed to notify winner: ' . $e->getMessage());
                    }
                }
            }
        }

        $this->info('Processed ' . $ended->count() . ' ended auction(s).');

        return 0;
    }
}
