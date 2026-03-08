<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecondChanceOfferNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Auction $auction,
        protected float $amount,
        protected string $paymentUrl
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Second Chance: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The winner did not complete payment. You are offered a second chance to purchase "' . $this->auction->title . '" for ₱' . number_format($this->amount, 2) . '.')
            ->action('View Offer', $this->paymentUrl)
            ->line('This offer may have a limited time to respond.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_second_chance',
            'title' => 'Second Chance Offer',
            'message' => 'You can purchase "' . $this->auction->title . '" for ₱' . number_format($this->amount, 2),
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'amount' => $this->amount,
            'action_url' => $this->paymentUrl,
        ];
    }
}
