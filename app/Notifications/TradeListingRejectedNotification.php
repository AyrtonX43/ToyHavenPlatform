<?php

namespace App\Notifications;

use App\Models\TradeListing;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeListingRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected TradeListing $listing;

    protected ?string $reason;

    public function __construct(TradeListing $listing, ?string $reason = null)
    {
        $this->listing = $listing;
        $this->reason = $reason;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Trade Listing Not Approved - ToyHaven')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your trade listing **"' . $this->listing->title . '"** was not approved for the marketplace.');

        if ($this->reason) {
            $message->line('**Reason:**')
                ->line($this->reason);
        }

        $message->line('You may create a new listing or contact our support team if you have questions about this decision.')
            ->action('Create New Listing', route('trading.listings.create'))
            ->line('Thank you for using ToyHaven Trading.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_rejected',
            'listing_id' => $this->listing->id,
            'listing_title' => $this->listing->title,
            'reason' => $this->reason,
            'message' => 'Your listing "' . $this->listing->title . '" was not approved.' . ($this->reason ? ' Reason: ' . $this->reason : ''),
        ];
    }
}
