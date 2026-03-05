<?php

namespace App\Notifications;

use App\Models\Trade;
use App\Models\TradeOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferAcceptedNotification extends Notification
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
        $conversation = $this->trade->conversation;
        $url = $conversation ? route('trading.conversations.show', $conversation) : route('trading.trades.show', $this->trade->id);
        return (new MailMessage)
            ->subject('Your Trade Offer Was Accepted - ToyHaven')
            ->line('Great news! Your offer on "' . $this->offer->tradeListing->title . '" was accepted.')
            ->line('You can now start the conversation with the seller.')
            ->action('Start Conversation', $url);
    }

    public function toArray(object $notifiable): array
    {
        $conversation = $this->trade->conversation;
        $actionUrl = $conversation ? route('trading.conversations.show', $conversation) : route('trading.trades.show', $this->trade->id);
        return [
            'type' => 'trade_offer_accepted',
            'title' => 'Offer Accepted',
            'message' => 'Your offer on "' . $this->offer->tradeListing->title . '" was accepted. You can now start the conversation.',
            'offer_id' => $this->offer->id,
            'trade_id' => $this->trade->id,
            'conversation_id' => $conversation?->id,
            'action_url' => $actionUrl,
        ];
    }
}
