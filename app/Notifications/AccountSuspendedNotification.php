<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountSuspendedNotification extends Notification
{
    use Queueable;

    protected $reason;
    protected $reportId;

    /**
     * Create a new notification instance.
     */
    public function __construct($reason = null, $reportId = null)
    {
        $this->reason = $reason;
        $this->reportId = $reportId;
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
            ->subject('Account Suspended - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We are writing to inform you that your account has been temporarily suspended from the ToyHaven Platform.');

        if ($this->reason) {
            $message->line('**Reason for suspension:**')
                    ->line($this->reason);
        }

        if ($this->reportId) {
            $message->line('This action was taken due to a report filed against your account.');
        }

        $message->line('During this suspension:')
            ->line('• Your account access is temporarily restricted')
            ->line('• You cannot make new purchases or listings')
            ->line('• Existing orders will continue to be processed')
            ->line('')
            ->line('If you believe this suspension was made in error, please contact our support team for assistance.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your understanding.');

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
            'reason' => $this->reason,
            'report_id' => $this->reportId,
        ];
    }
}
