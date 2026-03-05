<?php

namespace App\Notifications;

use App\Models\Trade;
use App\Models\TradeOffer;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferAcceptedNotification extends Notification
{
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
        $url = $this->trade->conversation
            ? route('trading.conversations.show', $this->trade->conversation)
            : route('trading.trades.show', $this->trade->id);

        return (new MailMessage)
            ->subject('Your Trade Offer Was Accepted - ToyHaven')
            ->line('Great news! Your offer on "' . $this->offer->tradeListing->title . '" was accepted.')
            ->line('You can now start a conversation to schedule your meetup.')
            ->action('Open Chat', $url);
    }

    public function toArray(object $notifiable): array
    {
        $actionUrl = $this->trade->conversation
            ? route('trading.conversations.show', $this->trade->conversation)
            : route('trading.trades.show', $this->trade->id);

        return [
            'type' => 'trade_offer_accepted',
            'title' => 'Offer Accepted',
            'message' => 'Your offer on "' . $this->offer->tradeListing->title . '" was accepted. You can now start a conversation.',
            'offer_id' => $this->offer->id,
            'trade_id' => $this->trade->id,
            'action_url' => $actionUrl,
        ];
    }
}
