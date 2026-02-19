<?php

namespace App\Events;

use App\Models\Auction;
use App\Models\AuctionBid;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionBidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Auction $auction,
        public AuctionBid $bid
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('auction.'.$this->auction->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AuctionBidPlaced';
    }

    public function broadcastWith(): array
    {
        return [
            'bid_id' => $this->bid->id,
            'amount' => (float) $this->bid->amount,
            'user_id' => $this->bid->user_id,
            'user_name' => $this->bid->user?->name,
        ];
    }
}
