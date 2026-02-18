<?php

namespace App\Notifications;

use App\Models\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeOfferAcceptedNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
        return (new MailMessage)
            ->subject('Your Trade Offer Was Accepted!')
            ->line('Great news! Your trade offer has been accepted.')
            ->line('Listing: ' . $this->trade->tradeListing->title)
            ->action('View Trade', route('trading.trades.show', $this->trade->id))
            ->line('Please provide your shipping address to proceed with the trade.');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'trade_offer_accepted',
            'trade_id' => $this->trade->id,
            'listing_title' => $this->trade->tradeListing->title,
            'message' => 'Your trade offer has been accepted!',
        ];
    }
}
