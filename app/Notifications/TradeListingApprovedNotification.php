<?php

namespace App\Notifications;

use App\Models\TradeListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeListingApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TradeListing $listing;

    public function __construct(TradeListing $listing)
    {
        $this->listing = $listing;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Trade Listing Approved - ToyHaven')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Great news! Your trade listing **"' . $this->listing->title . '"** has been approved by our admin team.')
            ->line('Your listing is now live on the marketplace and other users can view it and make trade offers.')
            ->action('View Your Listing', route('trading.listings.show', $this->listing->id))
            ->line('Thank you for using ToyHaven Trading!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_approved',
            'listing_id' => $this->listing->id,
            'listing_title' => $this->listing->title,
            'message' => 'Your listing "' . $this->listing->title . '" has been approved and is now live on the marketplace.',
        ];
    }
}
