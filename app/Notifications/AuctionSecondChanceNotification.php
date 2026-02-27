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
            ->greeting('You are the Backup Winner!')
            ->line("The original winner of **{$this->auction->title}** did not complete payment.")
            ->line("You can now purchase this item at your last bid price of **₱" . number_format($this->bidAmount, 2) . "**.")
            ->line('You have **24 hours** to accept and pay.')
            ->action('Claim Your Item', url('/auctions/' . $this->auction->id))
            ->line('Don\'t miss this second chance!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'auction_second_chance',
            'auction_id' => $this->auction->id,
            'title' => $this->auction->title,
            'amount' => $this->bidAmount,
            'message' => "Second chance! You can buy {$this->auction->title} at ₱" . number_format($this->bidAmount, 2),
        ];
    }
}
