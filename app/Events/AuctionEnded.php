<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionEnded implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Auction $auction
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel('auction.' . $this->auction->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AuctionEnded';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'winner_alias' => $this->auction->winner?->getAuctionAlias(),
            'winning_amount' => (float) $this->auction->winning_amount,
        ];
    }
}
