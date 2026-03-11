<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserWonAuction implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public Auction $auction,
        public ?AuctionPayment $payment = null,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('auction-user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserWonAuction';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'winning_amount' => (float) $this->auction->winning_amount,
            'winning_amount_formatted' => '₱' . number_format($this->auction->winning_amount, 2),
            'payment_link' => $this->payment
                ? route('auction.payment.show', $this->payment)
                : null,
            'message' => "Congratulations! You won \"{$this->auction->title}\"!",
        ];
    }
}
