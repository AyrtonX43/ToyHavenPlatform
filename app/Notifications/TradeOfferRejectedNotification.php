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

    protected TradeOffer $offer;

    public function __construct(TradeOffer $offer)
    {
        $this->offer = $offer;
    }

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Trade Offer Not Accepted - ToyHaven')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Your trade offer on the listing **"' . $this->offer->tradeListing->title . '"** was not accepted by the seller.')
            ->line('You can browse other listings or make a new offer.')
            ->action('Browse Trading', route('trading.index'))
            ->line('Thank you for using ToyHaven Trading!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_offer_rejected',
            'offer_id' => $this->offer->id,
            'listing_id' => $this->offer->trade_listing_id,
            'listing_title' => $this->offer->tradeListing->title,
            'message' => 'Your trade offer on "' . $this->offer->tradeListing->title . '" was not accepted.',
        ];
    }
}
