<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionOutbidNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Auction $auction,
        protected float $newBidAmount
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('You\'ve been outbid - ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('You have been outbid on "' . $this->auction->title . '".')
            ->line('Current high bid: ₱' . number_format($this->newBidAmount, 2))
            ->action('Place a Higher Bid', route('auction.show', $this->auction))
            ->line('Good luck!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_outbid',
            'title' => 'You\'ve been outbid',
            'message' => 'You were outbid on "' . $this->auction->title . '" (₱' . number_format($this->newBidAmount, 2) . ')',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'new_bid_amount' => $this->newBidAmount,
            'action_url' => route('auction.show', $this->auction),
        ];
    }
}
