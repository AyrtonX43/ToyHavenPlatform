<?php

namespace App\Notifications;

use App\Models\TradeOffer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferRejectedNotification extends Notification
{
    protected $offer;

    public function __construct(TradeOffer $offer)
    {
        $this->offer = $offer;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Trade Offer Rejected - ToyHaven')
            ->line('Your trade offer on the listing "' . $this->offer->tradeListing->title . '" was not accepted.')
            ->line('The seller has declined your offer. You may browse other listings or make a new offer.')
            ->action('View Trading', route('trading.index'))
            ->line('Thank you for using ToyHaven Trading.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'trade_offer_rejected',
            'offer_id' => $this->offer->id,
            'listing_id' => $this->offer->trade_listing_id,
            'listing_title' => $this->offer->tradeListing->title,
            'message' => 'Your trade offer on "' . $this->offer->tradeListing->title . '" was rejected.',
            'action_url' => route('trading.index'),
        ];
    }
}
