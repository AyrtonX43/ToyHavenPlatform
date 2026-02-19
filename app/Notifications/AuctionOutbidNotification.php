<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionBid;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionOutbidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Auction $auction,
        public AuctionBid $yourBid
    ) {}

    public function via($notifiable)
    {
        return ['database'];
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'auction_outbid',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'your_bid' => (float) $this->yourBid->amount,
            'message' => 'You were outbid on: ' . $this->auction->title,
        ];
    }
}
