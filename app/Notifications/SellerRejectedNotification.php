<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerRejectedNotification extends Notification
{
    use Queueable;

    protected $reason;
    protected $businessName;

    public function __construct($reason, $businessName)
    {
        $this->reason = $reason;
        $this->businessName = $businessName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Business Account Application Rejected - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We regret to inform you that your business account application for **' . $this->businessName . '** has been rejected on the ToyHaven Platform.')
            ->line('')
            ->line('**Reason for rejection:**')
            ->line($this->reason)
            ->line('')
            ->line('**What you can do:**')
            ->line('• Review the rejection reason carefully')
            ->line('• Address the issues mentioned')
            ->line('• Submit a new application with corrected information')
            ->line('• Contact our support team if you have questions')
            ->line('')
            ->line('If you believe this decision was made in error, please contact our support team for assistance.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your interest in ToyHaven.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'seller_rejected',
            'title' => 'Business Account Rejected',
            'message' => 'Your business account application for "' . $this->businessName . '" has been rejected.',
            'business_name' => $this->businessName,
            'reason' => $this->reason,
            'action_url' => route('seller.registration.index'),
        ];
    }
}
