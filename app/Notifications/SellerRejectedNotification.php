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

    /**
     * Create a new notification instance.
     */
    public function __construct($reason = null, $businessName = null)
    {
        $this->reason = $reason;
        $this->businessName = $businessName;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Business Account Application Rejected - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->businessName) {
            $message->line('We regret to inform you that your business account application for **' . $this->businessName . '** has been rejected on the ToyHaven Platform.');
        } else {
            $message->line('We regret to inform you that your business account application has been rejected on the ToyHaven Platform.');
        }

        if ($this->reason) {
            $message->line('**Reason for rejection:**')
                    ->line($this->reason);
        }

        $message->line('You may:')
            ->line('• Review the rejection reason and address any issues')
            ->line('• Submit a new application with corrected information')
            ->line('• Contact our support team if you have questions')
            ->line('')
            ->line('If you believe this decision was made in error, please contact our support team for assistance.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your interest in ToyHaven.');

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
            'type' => 'seller_rejected',
            'title' => 'Business Application Rejected',
            'message' => 'Your business account application' . ($this->businessName ? ' for ' . $this->businessName : '') . ' has been rejected.',
            'reason' => $this->reason,
            'business_name' => $this->businessName,
            'action_url' => url('/seller/registration'),
            'icon' => 'x-octagon',
            'color' => 'danger',
        ];
    }
}
