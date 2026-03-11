<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class UserOutbid implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $userId,
        public Auction $auction,
        public float $newAmount,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('auction-user.' . $this->userId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'UserOutbid';
    }

    public function broadcastWith(): array
    {
        return [
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'new_amount' => $this->newAmount,
            'new_amount_formatted' => '₱' . number_format($this->newAmount, 2),
            'link' => route('auction.show', $this->auction),
            'message' => "You've been outbid on \"{$this->auction->title}\"!",
        ];
    }
}
