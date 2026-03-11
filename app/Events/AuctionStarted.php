<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionStarted implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Auction $auction,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PresenceChannel('auction.' . $this->auction->id),
        ];
    }

    public function broadcastAs(): string
    {
        return 'AuctionStarted';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'title' => $this->auction->title,
            'starting_bid' => (float) $this->auction->starting_bid,
            'end_at' => $this->auction->end_at->toIso8601String(),
        ];
    }
}
