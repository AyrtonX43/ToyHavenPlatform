<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWonNotification extends Notification implements ShouldQueue
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
            ->subject('You won: ' . $this->auction->title)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('You won the auction for "' . $this->auction->title . '".')
            ->line('Please pay within 24 hours to complete your purchase.')
            ->action('Pay Now', $payUrl)
            ->line('Thank you for bidding on ToyHaven Auctions!');
    }

    public function toArray(object $notifiable): array
    {
        $payment = $this->auction->auctionPayments()->latest()->first();
        return [
            'type' => 'auction_won',
            'message' => 'You won: ' . $this->auction->title,
            'action_url' => $payment ? route('auctions.payment.index', $payment) : route('auctions.show', $this->auction),
            'auction_id' => $this->auction->id,
        ];
    }
}
