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
            ->subject('New Trade Offer Received')
            ->line('You have received a new trade offer on your listing: ' . $this->offer->tradeListing->title)
            ->action('View Offer', route('trading.offers.show', $this->offer->id))
            ->line('Thank you for using our platform!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'trade_offer_received',
            'offer_id' => $this->offer->id,
            'listing_id' => $this->offer->trade_listing_id,
            'listing_title' => $this->offer->tradeListing->title,
            'offerer_name' => $this->offer->offerer->name,
            'message' => 'You have received a new trade offer on your listing: ' . $this->offer->tradeListing->title,
        ];
    }
}
