<?php

namespace App\Notifications;

use App\Models\Trade;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferAcceptedNotification extends Notification
{
    protected $trade;

    public function __construct(Trade $trade)
    {
        $this->trade = $trade;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $chatUrl = $this->trade->conversation
            ? route('trading.conversations.show', $this->trade->conversation)
            : route('trading.trades.show', $this->trade->id);
        return (new MailMessage)
            ->subject('Your Trade Offer Was Accepted!')
            ->line('Great news! Your trade offer has been accepted.')
            ->line('Listing: ' . $this->trade->tradeListing->title)
            ->line('Both of you must proceed to chat to finalize the deal, then lock the offer to proceed with shipping.')
            ->action('Go to Trade Chat', $chatUrl)
            ->line('Thank you for using ToyHaven Trading!');
    }

    public function toArray($notifiable)
    {
        $chatUrl = $this->trade->conversation
            ? route('trading.conversations.show', $this->trade->conversation)
            : route('trading.trades.show', $this->trade->id);
        return [
            'type' => 'trade_offer_accepted',
            'trade_id' => $this->trade->id,
            'listing_title' => $this->trade->tradeListing->title,
            'title' => 'Your trade offer was accepted!',
            'message' => 'Your offer on "' . $this->trade->tradeListing->title . '" was accepted. Proceed to chat to finalize the deal, then lock to start shipping.',
            'action_url' => $chatUrl,
        ];
    }
}
