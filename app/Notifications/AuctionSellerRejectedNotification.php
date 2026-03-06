<?php

namespace App\Notifications;

use App\Models\AuctionSellerVerification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionSellerRejectedNotification extends Notification implements ShouldQueue
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
        $message = (new MailMessage)
            ->subject('Auction Seller Verification - Update')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('Unfortunately, your auction seller verification could not be approved at this time.');

        if ($this->verification->rejection_reason) {
            $message->line('**Reason:** ' . $this->verification->rejection_reason);
        }

        $message->line('You may submit a new application after addressing the feedback.')
            ->action('Submit New Application', route('auctions.verification.create'));

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'auction_seller_rejected',
            'message' => 'Your auction seller verification was not approved.',
            'rejection_reason' => $this->verification->rejection_reason,
            'action_url' => route('auctions.verification.create'),
        ];
    }
}
