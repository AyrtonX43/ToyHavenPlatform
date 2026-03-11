<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EscrowReleasedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Auction $auction,
        protected AuctionPayment $payment,
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
        return (new MailMessage)
            ->subject('Escrow released for: ' . $this->auction->title)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('The escrow for "' . $this->auction->title . '" has been released.')
            ->line('Amount: ₱' . number_format($this->payment->amount, 2))
            ->line('The funds have been credited to your account.')
            ->action('View Seller Dashboard', route('auction.seller.dashboard'))
            ->line('Thank you for selling on ToyHaven!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_escrow_released',
            'title' => 'Escrow Released',
            'message' => 'Escrow of ₱' . number_format($this->payment->amount, 2) . ' for "' . $this->auction->title . '" has been released.',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'action_url' => route('auction.seller.dashboard'),
        ];
    }
}
