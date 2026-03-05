<?php

namespace App\Notifications;

use App\Models\TradeOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TradeOffer $offer
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Trade Offer Was Declined - ToyHaven')
            ->line('Your offer on "' . $this->offer->tradeListing->title . '" was declined by the seller.')
            ->action('Browse Listings', route('trading.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_offer_rejected',
            'title' => 'Offer Declined',
            'message' => 'Your offer on "' . $this->offer->tradeListing->title . '" was declined.',
            'offer_id' => $this->offer->id,
            'listing_id' => $this->offer->trade_listing_id,
            'action_url' => route('trading.index'),
        ];
    }
}
