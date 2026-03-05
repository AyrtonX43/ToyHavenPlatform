<?php

namespace App\Notifications;

use App\Models\TradeOffer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferReceivedNotification extends Notification
{
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
            ->action('View Offer Details', route('trading.offers.show', $this->offer->id));
    }

    public function toArray(object $notifiable): array
    {
        $listing = $this->offer->tradeListing;
        $offererName = $this->offer->offerer->name ?? 'Someone';
        return [
            'type' => 'trade_offer_received',
            'title' => 'New Trade Offer',
            'message' => $offererName . ' made an offer on "' . $listing->title . '". View to accept or deny.',
            'offer_id' => $this->offer->id,
            'listing_id' => $listing->id,
            'action_url' => route('trading.offers.show', $this->offer->id),
        ];
    }
}
