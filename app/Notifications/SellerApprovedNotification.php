<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Schema;

class SellerApprovedNotification extends Notification
{
    use Queueable;

    protected $businessName;
    protected $shopType;

    /**
     * Create a new notification instance.
     */
    public function __construct($businessName = null, $shopType = 'Local Business Toyshop')
    {
        $this->businessName = $businessName;
        $this->shopType = $shopType;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // Use mail only for now - database notifications require migration
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $isVerified = $this->shopType === 'Verified Trusted Toyshop';
        
        $message = (new MailMessage)
            ->subject(($isVerified ? '✓ Verified Trusted Toyshop' : 'Business Registration') . ' Approved - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->businessName) {
            $message->line('🎉 Congratulations! Your ' . $this->shopType . ' registration for **' . $this->businessName . '** has been approved by our admin team on the ToyHaven Platform.');
        } else {
            $message->line('🎉 Congratulations! Your ' . $this->shopType . ' registration has been approved by our admin team on the ToyHaven Platform.');
        }
        
        if ($isVerified) {
            $message->line('**Your business is now marked as a Verified Trusted Toyshop!**')
                ->line('')
                ->line('✓ **Enhanced Benefits:**')
                ->line('• Verified badge displayed on your shop profile')
                ->line('• Priority customer support')
                ->line('• Featured placement in search results')
                ->line('• Enhanced trust and credibility with customers')
                ->line('• Access to advanced analytics and insights')
                ->line('• Higher customer conversion rates');
        } else {
            $message->line('Your business page is now active and ready to use!');
        }

        $message->line('')
            ->line('**You can now:**')
            ->line('• Create and manage your product listings')
            ->line('• Receive orders from customers')
            ->line('• Manage your business profile and settings')
            ->line('• Access your seller dashboard')
            ->line('• Build your brand and grow your business')
            ->line('')
            ->line('We are excited to have you as part of our marketplace!')
            ->action('Go to Seller Dashboard', url('/seller/dashboard'))
            ->line('Thank you for choosing ToyHaven!');

        return $message;
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $isVerified = $this->shopType === 'Verified Trusted Toyshop';
        
        return [
            'title' => $isVerified ? 'Verified Trusted Toyshop Approved!' : 'Business Registration Approved!',
            'message' => 'Congratulations! Your ' . $this->shopType . ' registration for ' . $this->businessName . ' has been approved.',
            'business_name' => $this->businessName,
            'shop_type' => $this->shopType,
            'action_url' => url('/seller/dashboard'),
            'action_text' => 'Go to Seller Dashboard',
            'type' => 'seller_approved',
        ];
    }
}
