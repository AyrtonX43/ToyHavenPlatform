<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApplicationSubmittedNotification extends Notification
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
            ->subject('Business Application Submitted - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Thank you for submitting your business account application for **' . $this->businessName . '** on the ToyHaven Platform.');

        if ($this->shopType === 'Verified Trusted Toyshop') {
            $message->line('**Application Type:** Verified Trusted Toyshop')
                ->line('')
                ->line('Your application includes verification for:')
                ->line('✓ Business identity and credentials')
                ->line('✓ Legal business documents')
                ->line('✓ Product authenticity verification')
                ->line('✓ Enhanced seller benefits');
        } else {
            $message->line('**Application Type:** Local Business Toyshop');
        }

        $message->line('')
            ->line('**What happens next:**')
            ->line('• Our admin team will review your application and documents')
            ->line('• This process typically takes 1-3 business days')
            ->line('• You will receive an email notification once your application is reviewed')
            ->line('• You can check your application status in your seller dashboard')
            ->line('')
            ->line('**During the review process:**')
            ->line('• You can view your application status')
            ->line('• You cannot create product listings yet')
            ->line('• All seller features will be unlocked once approved')
            ->action('View Application Status', url('/seller/dashboard'))
            ->line('')
            ->line('If you have any questions, please don\'t hesitate to contact our support team.')
            ->line('Thank you for choosing ToyHaven!');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trade_listing_submitted',
            'title' => 'Business Application Submitted',
            'message' => 'Your business application for "' . $this->businessName . '" has been submitted and is pending admin approval.',
            'business_name' => $this->businessName,
            'shop_type' => $this->shopType,
            'action_url' => route('seller.dashboard'),
        ];
    }
}
