<?php

namespace App\Notifications;

use App\Models\Auction;
use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWonSellerNotification extends Notification
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
        $winnerName = $this->payment->winner?->name ?? 'A bidder';
        $dashboardUrl = route('auction.seller.dashboard');

        return (new MailMessage)
            ->subject('Your auction sold: ' . $this->auction->title)
            ->greeting('Great news, ' . $notifiable->name . '!')
            ->line('Your auction "' . $this->auction->title . '" has ended and sold.')
            ->line($winnerName . ' won with a bid of ₱' . number_format($this->payment->amount, 2))
            ->line('They have 48 hours to complete payment. Once paid, you can ship the item from your dashboard.')
            ->action('View Seller Dashboard', $dashboardUrl)
            ->line('Thank you for selling on ToyHaven Auctions!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_sold',
            'title' => 'Auction Sold',
            'message' => 'Your auction "' . $this->auction->title . '" sold for ₱' . number_format($this->payment->amount, 2) . '. Awaiting winner payment.',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'payment_id' => $this->payment->id,
            'amount' => $this->payment->amount,
            'action_url' => route('auction.seller.dashboard'),
        ];
    }
}
