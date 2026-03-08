<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionSellerApprovedNotification extends Notification
{
    use Queueable;

    protected string $type;
    protected ?string $businessName;

    public function __construct(string $type, ?string $businessName = null)
    {
        $this->type = $type;
        $this->businessName = $businessName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Auction Seller Registration Approved - ToyHaven Platform')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your auction seller registration has been approved on the ToyHaven Platform.');

        if ($this->type === 'business' && $this->businessName) {
            $message->line('**Account Type:** Business Auction Seller - ' . $this->businessName);
        } else {
            $message->line('**Account Type:** Individual Auction Seller');
        }

        $message->line('')
            ->line('You can now list and sell items in the auction.')
            ->action('Go to Auction Hub', route('auction.index'))
            ->line('Thank you for joining ToyHaven Auctions!');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->type === 'business' && $this->businessName
            ? 'Auction Business Seller Approved'
            : 'Auction Individual Seller Approved';

        $message = $this->type === 'business' && $this->businessName
            ? 'Your auction business seller registration for "' . $this->businessName . '" has been approved!'
            : 'Your auction individual seller registration has been approved!';

        return [
            'type' => 'auction_seller_approved',
            'title' => $title,
            'message' => $message,
            'verification_type' => $this->type,
            'business_name' => $this->businessName,
            'action_url' => route('auction.index'),
        ];
    }
}
