<?php

namespace App\Events;

use App\Models\Auction;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AuctionEnded implements ShouldBroadcastNow
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
        return 'AuctionEnded';
    }

    public function broadcastWith(): array
    {
        $winnerAlias = null;
        if ($this->auction->winner_id) {
            $winnerBid = $this->auction->bids()->where('user_id', $this->auction->winner_id)->first();
            $winnerAlias = $winnerBid ? 'Bidder #' . $winnerBid->rank_at_bid : 'Winner';
        }

        return [
            'auction_id' => $this->auction->id,
            'outcome' => $this->auction->auction_outcome,
            'winner_alias' => $winnerAlias,
            'winner_id' => $this->auction->winner_id,
            'final_price' => $this->auction->winning_amount ? (float) $this->auction->winning_amount : null,
            'final_price_formatted' => $this->auction->winning_amount
                ? '₱' . number_format($this->auction->winning_amount, 2)
                : null,
        ];
    }
}
