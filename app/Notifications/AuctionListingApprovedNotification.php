<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionListingApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Auction $auction
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Approved: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your auction "' . $this->auction->title . '" has been approved and is now live.')
            ->action('View Auction', route('auctions.show', $this->auction))
            ->line('Thank you for selling with ToyHaven Auctions!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_listing_approved',
            'message' => 'Your auction "' . $this->auction->title . '" has been approved and is now live.',
            'action_url' => route('auctions.show', $this->auction),
            'auction_id' => $this->auction->id,
        ];
    }
}
