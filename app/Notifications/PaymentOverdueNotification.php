<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentOverdueNotification extends Notification
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
        return (new MailMessage)
            ->subject('Payment overdue for: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name)
            ->line('Your payment for "' . $this->auction->title . '" has passed the deadline.')
            ->line('Amount: ₱' . number_format($this->payment->amount, 2))
            ->line('This has been recorded as an offense. Repeated offenses may result in auction access suspension.')
            ->action('View Auctions', route('auction.index'))
            ->line('If you believe this is an error, please contact support.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_payment_overdue',
            'title' => 'Payment Overdue',
            'message' => 'Your payment of ₱' . number_format($this->payment->amount, 2) . ' for "' . $this->auction->title . '" was not completed in time.',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'action_url' => route('auction.index'),
        ];
    }
}
