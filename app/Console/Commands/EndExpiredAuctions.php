<?php

namespace App\Console\Commands;

use App\Models\Auction;
use App\Models\AuctionBid;
use App\Models\AuctionPayment;
use App\Events\AuctionEnded;
use App\Notifications\AuctionWonNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EndExpiredAuctions extends Command
{
    protected $signature = 'auction:end-expired';

    protected $description = 'Finalize auctions that have passed end_at, set winner, create payment';

    public function handle(): int
    {
        $expired = Auction::where('status', 'live')
            ->whereNotNull('end_at')
            ->where('end_at', '<=', now())
            ->get();

        $count = 0;

        foreach ($expired as $auction) {
            try {
                DB::transaction(function () use ($auction, &$count) {
                    $highestBid = $auction->bids()->orderByDesc('amount')->first();

                    if ($highestBid) {
                        $meetsReserve = ! $auction->reserve_price || $highestBid->amount >= $auction->reserve_price;

                        if ($meetsReserve) {
                            AuctionBid::where('auction_id', $auction->id)->update(['is_winning' => false]);
                            $highestBid->update(['is_winning' => true]);

                            $auction->update([
                                'status' => 'ended',
                                'winner_id' => $highestBid->user_id,
                                'winning_amount' => $highestBid->amount,
                            ]);

                            $plan = $highestBid->user->currentPlan();
                            $buyerPremiumRate = $plan ? $plan->getBuyersPremiumRate() : 5;
                            $buyerPremium = round($highestBid->amount * ($buyerPremiumRate / 100), 2);
                            $totalAmount = $highestBid->amount + $buyerPremium;
                            $platformFee = round($totalAmount * 0.05, 2);
                            $sellerPayout = $highestBid->amount - $platformFee;

                            $seller = $auction->getSellerUser();
                            AuctionPayment::create([
                                'auction_id' => $auction->id,
                                'winner_id' => $highestBid->user_id,
                                'seller_user_id' => $seller->id,
                                'seller_paypal_email' => $auction->auctionSellerProfile?->paypal_email ?? $seller->email,
                                'bid_amount' => $highestBid->amount,
                                'buyer_premium' => $buyerPremium,
                                'total_amount' => $totalAmount,
                                'platform_fee' => $platformFee,
                                'seller_payout' => $sellerPayout,
                                'payment_status' => 'pending',
                                'escrow_status' => 'awaiting_payment',
                                'payment_deadline' => now()->addHours(24),
                            ]);

                            try {
                                $highestBid->user->notify(new AuctionWonNotification($auction));
                            } catch (\Exception $e) {
                                Log::warning('Failed to send auction won notification', ['error' => $e->getMessage()]);
                            }
                        } else {
                            $auction->update(['status' => 'ended']);
                        }
                    } else {
                        $auction->update(['status' => 'ended']);
                    }

                    event(new AuctionEnded($auction->fresh()));
                    $count++;
                });
            } catch (\Exception $e) {
                Log::error('Failed to end auction', ['auction_id' => $auction->id, 'error' => $e->getMessage()]);
            }
        }

        if ($count > 0) {
            $this->info("Ended {$count} auction(s).");
        }

        return self::SUCCESS;
    }
}
