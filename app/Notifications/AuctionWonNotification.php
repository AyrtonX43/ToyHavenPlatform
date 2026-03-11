<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWonNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Auction $auction,
        protected AuctionPayment $payment
    ) {}

    public function via(object $notifiable): array
    {
        $channels = ['database'];

        try {
            if (config('mail.default') && config('mail.default') !== 'log') {
                $channels[] = 'mail';
            }
        } catch (\Throwable $e) {}

        return $channels;
    }

    public function toMail(object $notifiable): MailMessage
    {
        $payUrl = route('auction.payment.show', $this->payment);
        $deadline = $this->payment->payment_deadline?->format('M d, Y H:i');

        return (new MailMessage)
            ->subject('You won: ' . $this->auction->title)
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('You won the auction for "' . $this->auction->title . '".')
            ->line('Amount to pay: ₱' . number_format($this->payment->amount, 2))
            ->line('Payment deadline: ' . $deadline)
            ->action('Pay Now', $payUrl)
            ->line('Thank you for using ToyHaven Auctions!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_won',
            'title' => 'Auction Won',
            'message' => 'You won "' . $this->auction->title . '" - Pay ₱' . number_format($this->payment->amount, 2) . ' by ' . $this->payment->payment_deadline?->format('M d'),
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'action_url' => route('auction.payment.show', $this->payment),
        ];
    }
}
