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
        public Auction $auction,
        public float $bidAmount
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Second Chance: ' . $this->auction->title)
            ->greeting('Good news!')
            ->line('The winning bidder did not pay. You have a second chance to win **' . $this->auction->title . '** at ₱' . number_format($this->bidAmount, 0) . '.')
            ->line('You have **24 hours** to complete payment.')
            ->action('Pay Now', route('auctions.payment.second-chance.show', $this->auction))
            ->line('Thank you for bidding on ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'auction_second_chance',
            'auction_id' => $this->auction->id,
            'amount' => $this->bidAmount,
            'message' => "Second chance to win {$this->auction->title} at ₱" . number_format($this->bidAmount, 0),
        ];
    }
}
