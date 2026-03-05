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

    public function __construct(
        public TradeListing $listing,
        public ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $msg = (new MailMessage)
            ->subject('Your Trade Listing Was Not Approved - ToyHaven')
            ->line('Unfortunately, your listing "' . $this->listing->title . '" was not approved.');
        if ($this->reason) {
            $msg->line('Reason: ' . $this->reason);
        }
        return $msg->action('View My Listings', route('trading.listings.my'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_rejected',
            'title' => 'Listing Rejected',
            'message' => 'Your listing "' . $this->listing->title . '" was not approved.' . ($this->reason ? ' Reason: ' . $this->reason : ''),
            'listing_id' => $this->listing->id,
            'rejection_reason' => $this->reason,
            'action_url' => route('trading.listings.my'),
        ];
    }
}
