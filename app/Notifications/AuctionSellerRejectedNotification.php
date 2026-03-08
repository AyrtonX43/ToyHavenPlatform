<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionSellerRejectedNotification extends Notification
{
    use Queueable;

    protected string $type;
    protected string $feedback;
    protected ?string $businessName;

    public function __construct(string $type, string $feedback, ?string $businessName = null)
    {
        $this->type = $type;
        $this->feedback = $feedback;
        $this->businessName = $businessName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Auction Seller Registration Rejected - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We regret to inform you that your auction seller registration has been rejected on the ToyHaven Platform.');

        if ($this->type === 'business' && $this->businessName) {
            $message->line('**Application:** Business Auction Seller - ' . $this->businessName);
        } else {
            $message->line('**Application:** Individual Auction Seller');
        }

        $message->line('')
            ->line('**Feedback / Reason:**')
            ->line($this->feedback)
            ->line('')
            ->line('**What you can do:**')
            ->line('• Review the feedback carefully')
            ->line('• Address the issues mentioned')
            ->line('• Submit a new application with corrected documents')
            ->line('• Contact our support team if you have questions')
            ->action('Submit New Application', route('auction.index'))
            ->line('Thank you for your interest in ToyHaven Auctions.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        $title = $this->type === 'business' && $this->businessName
            ? 'Auction Business Seller Rejected'
            : 'Auction Individual Seller Rejected';

        $message = $this->type === 'business' && $this->businessName
            ? 'Your auction business seller registration for "' . $this->businessName . '" has been rejected.'
            : 'Your auction individual seller registration has been rejected.';

        return [
            'type' => 'auction_seller_rejected',
            'title' => $title,
            'message' => $message,
            'feedback' => $this->feedback,
            'verification_type' => $this->type,
            'business_name' => $this->businessName,
            'action_url' => route('auction.index'),
        ];
    }
}
