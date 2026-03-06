<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionSecondChanceNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Auction $auction
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payment = $this->auction->auctionPayments()->latest()->first();
        $payUrl = $payment ? route('auctions.payment.index', $payment) : route('auctions.show', $this->auction);
        return (new MailMessage)
            ->subject('Second Chance: You won ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The original winner did not pay in time. You are now the winner of "' . $this->auction->title . '".')
            ->line('Please pay within 24 hours to complete your purchase.')
            ->action('Pay Now', $payUrl)
            ->line('Thank you!');
    }

    public function toArray(object $notifiable): array
    {
        $payment = $this->auction->auctionPayments()->latest()->first();
        return [
            'type' => 'auction_second_chance',
            'message' => 'Second chance: You won ' . $this->auction->title,
            'action_url' => $payment ? route('auctions.payment.index', $payment) : route('auctions.show', $this->auction),
            'auction_id' => $this->auction->id,
        ];
    }
}
