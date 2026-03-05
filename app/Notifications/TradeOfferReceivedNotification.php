<?php

namespace App\Notifications;

use App\Models\TradeOffer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Str;
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
        $offererName = $this->offer->offerer->name ?? 'Someone';
        return (new MailMessage)
            ->subject('New Offer on Your Listing - ToyHaven')
            ->line($offererName . ' made an offer on your listing "' . $listing->title . '".')
            ->action('View in Offers Received', route('trading.offers.received'));
    }

    public function toArray(object $notifiable): array
    {
        $listing = $this->offer->tradeListing;
        $offererName = $this->offer->offerer->name ?? 'Someone';
        return [
            'type' => 'trade_offer_received',
            'title' => 'New Offer on Your Listing',
            'message' => $offererName . ' made an offer on "' . Str::limit($listing->title, 40) . '". View in Offers Received.',
            'offer_id' => $this->offer->id,
            'listing_id' => $listing->id,
            'action_url' => route('trading.offers.received'),
        ];
    }
}
