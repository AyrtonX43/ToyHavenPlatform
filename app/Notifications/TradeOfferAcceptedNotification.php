<?php

namespace App\Notifications;

use App\Models\Trade;
use App\Models\TradeOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public TradeOffer $offer,
        public Trade $trade
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your Trade Offer Was Accepted - ToyHaven')
            ->line('Great news! Your offer on "' . $this->offer->tradeListing->title . '" was accepted.')
            ->action('View Trade', route('trading.trades.show', $this->trade->id));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_offer_accepted',
            'title' => 'Offer Accepted',
            'message' => 'Your offer on "' . $this->offer->tradeListing->title . '" was accepted. Schedule your meetup.',
            'offer_id' => $this->offer->id,
            'trade_id' => $this->trade->id,
            'action_url' => route('trading.trades.show', $this->trade->id),
        ];
    }
}
