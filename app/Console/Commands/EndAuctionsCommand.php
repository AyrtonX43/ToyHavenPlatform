<?php

namespace App\Console\Commands;

use App\Events\AuctionEnded;
use App\Events\UserWonAuction;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Notifications\AuctionWonNotification;
use App\Notifications\AuctionWonSellerNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class EndAuctionsCommand extends Command
{
    protected $signature = 'auctions:end';

    protected $description = 'Mark ended auctions, create payment records, broadcast events, and notify winners';

    public function handle(): int
    {
        $ended = Auction::where('status', 'active')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->get();

        foreach ($ended as $auction) {
            $outcome = $auction->winner_id
                ? ($auction->meetsReserve() ? 'sold' : 'reserve_not_met')
                : 'no_bids';

            $auction->update(['status' => 'ended', 'auction_outcome' => $outcome]);

            try {
                broadcast(new AuctionEnded($auction));
            } catch (\Exception $e) {
                Log::error('Failed to broadcast AuctionEnded', [
                    'auction_id' => $auction->id,
                    'error' => $e->getMessage(),
                ]);
            }

            if ($auction->winner_id && $auction->meetsReserve()) {
                $existing = AuctionPayment::where('auction_id', $auction->id)
                    ->where('winner_id', $auction->winner_id)
                    ->first();

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

                    try {
                        $auction->user?->notify(new AuctionWonSellerNotification($auction, $payment));
                    } catch (\Exception $e) {
                        $this->error('Failed to notify seller: ' . $e->getMessage());
                    }

                    try {
                        broadcast(new UserWonAuction($auction->winner_id, $auction, $payment));
                    } catch (\Exception $e) {
                        Log::error('Failed to broadcast UserWonAuction', [
                            'auction_id' => $auction->id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                }
            }

            $this->notifyLosingBidders($auction);
        }

        $this->info('Processed ' . $ended->count() . ' ended auction(s).');

        return 0;
    }

    private function notifyLosingBidders(Auction $auction): void
    {
        if (! $auction->winner_id) {
            return;
        }

        $losingBidderIds = AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', '!=', $auction->winner_id)
            ->groupBy('user_id')
            ->pluck('user_id');

        foreach ($losingBidderIds as $userId) {
            try {
                broadcast(new \App\Events\UserOutbid($userId, $auction, (float) $auction->winning_amount));
            } catch (\Exception $e) {
                Log::error('Failed to broadcast auction ended to losing bidder', [
                    'auction_id' => $auction->id,
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
