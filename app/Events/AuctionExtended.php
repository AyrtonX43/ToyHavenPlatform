<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionExtended implements ShouldBroadcastNow
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
        return 'AuctionExtended';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'end_at' => $this->auction->end_at?->toIso8601String(),
            'message' => 'Time extended! Anti-snipe protection activated.',
        ];
    }
}
