<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerApprovedNotification extends Notification
{
    use Queueable;

    protected $businessName;

    /**
     * Create a new notification instance.
     */
    public function __construct($businessName = null)
    {
        $this->businessName = $businessName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Business Registration Approved - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->businessName) {
            $message->line('Congratulations! Your business registration for **' . $this->businessName . '** has been approved by our admin team on the ToyHaven Platform.');
        } else {
            $message->line('Congratulations! Your business registration has been approved by our admin team on the ToyHaven Platform.');
        }
        
        $message->line('Your business page is now active and ready to use!');

        $message->line('You can now:')
            ->line('• Create and manage your product listings')
            ->line('• Receive orders from customers')
            ->line('• Manage your business profile and settings')
            ->line('• Access your seller dashboard')
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
        return [
            'business_name' => $this->businessName,
        ];
    }
}
