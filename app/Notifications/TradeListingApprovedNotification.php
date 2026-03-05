<?php

namespace App\Notifications;

use App\Models\TradeListing;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeListingApprovedNotification extends Notification
{
    use Queueable;

    public function __construct(
        public TradeListing $listing
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Trade Listing Has Been Approved - ToyHaven')
            ->line('Good news! Your listing "' . $this->listing->title . '" has been approved and is now live on the marketplace.')
            ->action('View Listing', route('trading.listings.show', $this->listing->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_approved',
            'title' => 'Listing Approved',
            'message' => 'Your listing "' . $this->listing->title . '" is now live.',
            'listing_id' => $this->listing->id,
            'action_url' => route('trading.listings.show', $this->listing->id),
        ];
    }
}
