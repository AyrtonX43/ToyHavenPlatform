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
            ->subject('Trade Listing Submitted for Review - ToyHaven')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your trade listing **"' . $this->listing->title . '"** has been submitted and will be reviewed by an admin before it goes live.')
            ->line('You can view or edit your listing from your notifications while it is pending. Once approved, it will appear on the marketplace and other users can make offers.')
            ->action('Open Notifications', route('notifications.index'))
            ->line('Thank you for using ToyHaven Trading!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_submitted',
            'listing_id' => $this->listing->id,
            'listing_title' => $this->listing->title,
            'message' => 'Your listing "' . $this->listing->title . '" has been submitted and is pending admin review.',
        ];
    }
}
