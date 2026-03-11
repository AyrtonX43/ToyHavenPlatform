<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Auction $auction,
        public float $amount,
        public string $bidderAlias,
        public int $bidCount,
        public float $nextMinBid,
        public string $endAt,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('auction.' . $this->auction->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'BidPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'amount' => $this->amount,
            'amount_formatted' => '₱' . number_format($this->amount, 2),
            'bidder_alias' => $this->bidderAlias,
            'bid_count' => $this->bidCount,
            'next_min_bid' => $this->nextMinBid,
            'next_min_bid_formatted' => '₱' . number_format($this->nextMinBid, 2),
            'end_at' => $this->endAt,
        ];
    }
}
