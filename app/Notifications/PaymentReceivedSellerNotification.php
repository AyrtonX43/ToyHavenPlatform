<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentReceivedSellerNotification extends Notification
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
            ->subject('Payment received for: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The buyer has paid for "' . $this->auction->title . '".')
            ->line('Amount: ₱' . number_format($this->payment->amount, 2))
            ->line('Funds are held in escrow until the buyer confirms delivery.')
            ->action('View Seller Dashboard', route('auction.seller.dashboard'))
            ->line('Please ship the item as soon as possible.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_payment_received',
            'title' => 'Payment Received',
            'message' => 'Buyer paid ₱' . number_format($this->payment->amount, 2) . ' for "' . $this->auction->title . '". Please ship the item.',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'action_url' => route('auction.seller.dashboard'),
        ];
    }
}
