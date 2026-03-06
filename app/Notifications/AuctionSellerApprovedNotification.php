<?php

namespace App\Notifications;

use App\Models\AuctionSellerVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionSellerApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AuctionSellerVerification $verification
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Auction Seller Verification Approved')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your auction seller verification has been approved.')
            ->line('You can now list auctions on ToyHaven.')
            ->action('List an Auction', route('auctions.seller.create'))
            ->line('Thank you for joining ToyHaven Auctions!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_seller_approved',
            'message' => 'Your auction seller verification has been approved.',
            'action_url' => route('auctions.seller.create'),
        ];
    }
}
