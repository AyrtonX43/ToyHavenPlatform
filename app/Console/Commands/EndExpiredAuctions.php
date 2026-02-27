<?php

namespace App\Console\Commands;

use App\Events\AuctionEnded;
use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Notifications\AuctionLostNotification;
use App\Notifications\AuctionWonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EndExpiredAuctions extends Command
{
    protected $signature = 'auction:end-expired';

    protected $description = 'Finalize auctions that have passed their end time, determine winners, and trigger payment flows';

    public function handle(): int
    {
        $expiredAuctions = Auction::where('status', 'live')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expiredAuctions as $auction) {
            try {
                DB::transaction(function () use ($auction) {
                    $highestBid = $auction->bids()->orderByDesc('amount')->first();

                    if ($highestBid) {
                        $meetsReserve = ! $auction->reserve_price || $highestBid->amount >= $auction->reserve_price;

                        if ($meetsReserve) {
                            $auction->update([
                                'status' => 'ended',
                                'winner_id' => $highestBid->user_id,
                                'winning_amount' => $highestBid->amount,
                            ]);

                            $buyerPremiumRate = Auction::buyerPremiumRate($highestBid->user);
                            $buyerPremium = round($highestBid->amount * ($buyerPremiumRate / 100), 2);
                            $totalAmount = $highestBid->amount + $buyerPremium;
                            $platformFee = round($totalAmount * 0.05, 2);

                            AuctionPayment::create([
                                'auction_id' => $auction->id,
                                'winner_id' => $highestBid->user_id,
                                'seller_user_id' => $auction->user_id,
                                'bid_amount' => $highestBid->amount,
                                'buyer_premium' => $buyerPremium,
                                'total_amount' => $totalAmount,
                                'platform_fee' => $platformFee,
                                'seller_payout' => $highestBid->amount - $platformFee,
                                'payment_status' => 'pending',
                                'escrow_status' => 'awaiting_payment',
                                'payment_deadline' => now()->addHours(24),
                            ]);

                            try {
                                $highestBid->user->notify(new AuctionWonNotification($auction));
                            } catch (\Exception $e) {
                                Log::warning('Failed to send auction won notification', [
                                    'auction_id' => $auction->id,
                                    'error' => $e->getMessage(),
                                ]);
                            }
                        } else {
                            $auction->update(['status' => 'ended']);
                        }
                    } else {
                        $auction->update(['status' => 'ended']);
                    }

                    // Notify non-winning bidders
                    $this->notifyLosers($auction);

                    event(new AuctionEnded($auction->fresh()));
                });

                $count++;
            } catch (\Exception $e) {
                Log::error('Failed to end auction', [
                    'auction_id' => $auction->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        if ($count > 0) {
            $this->info("Ended {$count} auction(s).");
        }

        return self::SUCCESS;
    }

    private function notifyLosers(Auction $auction): void
    {
        $loserIds = AuctionBid::where('auction_id', $auction->id)
            ->where('user_id', '!=', $auction->winner_id ?? 0)
            ->distinct('user_id')
            ->pluck('user_id');

        $similarIds = Auction::where('id', '!=', $auction->id)
            ->where('category_id', $auction->category_id)
            ->where('status', 'live')
            ->limit(3)
            ->pluck('id')
            ->toArray();

        foreach ($loserIds as $userId) {
            try {
                $user = \App\Models\User::find($userId);
                if ($user) {
                    $user->notify(new AuctionLostNotification($auction, $similarIds));
                }
            } catch (\Exception $e) {
                Log::warning('Failed to send auction lost notification', [
                    'user_id' => $userId,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
}
