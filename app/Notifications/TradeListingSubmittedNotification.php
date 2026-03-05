<?php

namespace App\Notifications;

use App\Models\TradeListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeListingSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('New Trade Listing Pending Review - ToyHaven')
            ->line('A new trade listing "' . $this->listing->title . '" has been submitted and requires your review.')
            ->action('Review Listings', route('admin.trades.listings', ['status' => 'pending_approval']));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_submitted',
            'title' => 'New Trade Listing Pending Review',
            'message' => 'Listing "' . $this->listing->title . '" awaits approval.',
            'listing_id' => $this->listing->id,
            'action_url' => route('admin.trades.listings', ['status' => 'pending_approval']),
        ];
    }
}
