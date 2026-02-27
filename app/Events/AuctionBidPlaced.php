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
            'user_alias' => $this->bid->user?->getAuctionAlias() ?? 'Anonymous',
            'bids_count' => $this->auction->bids()->count(),
            'min_next_bid' => $this->auction->getMinNextBid(),
        ];
    }
}
