<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApprovedNotification extends Notification
{
    use Queueable;

    protected $shopType;
    protected $businessName;

    public function __construct($shopType, $businessName)
    {
        $this->shopType = $shopType;
        $this->businessName = $businessName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Business Account Approved - ToyHaven Platform')
            ->greeting('Congratulations ' . $notifiable->name . '!')
            ->line('Your business account application for **' . $this->businessName . '** has been approved on the ToyHaven Platform.');

        if ($this->shopType === 'Verified Trusted Toyshop') {
            $message->line('**Account Type:** Verified Trusted Toyshop')
                ->line('')
                ->line('**Your Verified Benefits:**')
                ->line('✓ Verified badge on your business profile')
                ->line('✓ Higher visibility in search results')
                ->line('✓ Priority customer support')
                ->line('✓ Access to premium seller tools')
                ->line('✓ Increased buyer trust and credibility');
        } else {
            $message->line('**Account Type:** Local Business Toyshop')
                ->line('')
                ->line('You can now start selling on our platform!');
        }

        $message->line('')
            ->line('You can now:')
            ->line('• Create and manage product listings')
            ->line('• Receive and process orders')
            ->line('• Track your sales and analytics')
            ->line('• Communicate with customers')
            ->action('Go to Seller Dashboard', url('/seller/dashboard'))
            ->line('Thank you for joining ToyHaven!');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'seller_approved',
            'title' => 'Business Account Approved',
            'message' => 'Your business account "' . $this->businessName . '" has been approved!',
            'business_name' => $this->businessName,
            'shop_type' => $this->shopType,
            'action_url' => route('seller.dashboard'),
        ];
    }
}
