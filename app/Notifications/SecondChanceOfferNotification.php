<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SecondChanceOfferNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Auction $auction,
        protected AuctionPayment $payment,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $paymentUrl = route('auction.payment.show', $this->payment);

        return (new MailMessage)
            ->subject('Second Chance: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('The winner did not complete payment. You are offered a second chance to purchase "' . $this->auction->title . '" for ₱' . number_format($this->payment->amount, 2) . '.')
            ->action('View Offer', $paymentUrl)
            ->line('You have 48 hours to complete payment.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_second_chance',
            'title' => 'Second Chance Offer',
            'message' => 'You can purchase "' . $this->auction->title . '" for ₱' . number_format($this->payment->amount, 2),
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'action_url' => route('auction.payment.show', $this->payment),
        ];
    }
}
