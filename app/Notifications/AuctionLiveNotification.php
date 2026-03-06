<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionLiveNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Auction $auction) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction is Live: ' . $this->auction->title)
            ->line('An auction you saved is now live!')
            ->action('View Auction', route('auctions.show', $this->auction))
            ->line('Thank you for using ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'auction_live',
            'auction_id' => $this->auction->id,
            'title' => $this->auction->title,
            'message' => "Auction \"{$this->auction->title}\" is now live!",
        ];
    }
}
