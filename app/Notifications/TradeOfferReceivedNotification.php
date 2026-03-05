<?php

namespace App\Notifications;

use App\Models\TradeOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferReceivedNotification extends Notification implements ShouldQueue
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
        $listing = $this->offer->tradeListing;
        return (new MailMessage)
            ->subject('You Received a New Trade Offer - ToyHaven')
            ->line('You received a new offer on your listing "' . $listing->title . '".')
            ->action('View Offer', route('trading.offers.received'));
    }

    public function toArray(object $notifiable): array
    {
        $listing = $this->offer->tradeListing;
        return [
            'type' => 'trade_offer_received',
            'title' => 'New Trade Offer',
            'message' => 'You received an offer on "' . $listing->title . '".',
            'offer_id' => $this->offer->id,
            'listing_id' => $listing->id,
            'action_url' => route('trading.offers.received'),
        ];
    }
}
