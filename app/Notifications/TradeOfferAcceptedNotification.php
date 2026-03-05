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
        $conversation = $this->trade->conversation;
        $url = $conversation ? route('trading.conversations.show', $conversation) : route('trading.trades.show', $this->trade->id);
        return (new MailMessage)
            ->subject('Your Trade Offer Was Accepted - ToyHaven')
            ->line('Great news! Your offer on "' . $this->offer->tradeListing->title . '" was accepted.')
            ->line('You can start the conversation in chat now.')
            ->action('Offer Trade Approval', $url);
    }

    public function toArray(object $notifiable): array
    {
        $conversation = $this->trade->conversation;
        $actionUrl = $conversation ? route('trading.conversations.show', $conversation) : route('trading.trades.show', $this->trade->id);
        return [
            'type' => 'trade_offer_accepted',
            'title' => 'Offer Trade Approval',
            'message' => 'Your offer on "' . $this->offer->tradeListing->title . '" was accepted. You can start the conversation in chat.',
            'offer_id' => $this->offer->id,
            'trade_id' => $this->trade->id,
            'action_url' => $actionUrl,
        ];
    }
}
