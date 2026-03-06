<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionListingRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Auction $auction,
        public string $feedback
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Rejected: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your auction "' . $this->auction->title . '" could not be approved.')
            ->line('**Feedback:** ' . $this->feedback)
            ->action('Create New Auction', route('auctions.seller.create'))
            ->line('You may edit and resubmit your listing. Thank you for your understanding.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_listing_rejected',
            'message' => 'Your auction "' . $this->auction->title . '" was rejected. ' . $this->feedback,
            'action_url' => route('auctions.seller.create'),
            'auction_id' => $this->auction->id,
        ];
    }
}
