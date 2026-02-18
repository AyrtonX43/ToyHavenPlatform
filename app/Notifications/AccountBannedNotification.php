<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AccountBannedNotification extends Notification
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
            ->subject('Account Banned - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We are writing to inform you that your account has been banned from the ToyHaven Platform.');

        if ($this->reason) {
            $message->line('**Reason for ban:**')
                    ->line($this->reason);
        }

        if ($this->reportId) {
            $message->line('This action was taken due to a report filed against your account.');
        }

        $message->line('As a result of this ban:')
            ->line('• You will no longer be able to access your account')
            ->line('• All pending orders will be cancelled')
            ->line('• You cannot make new purchases or listings')
            ->line('')
            ->line('If you believe this ban was made in error, please contact our support team for assistance.')
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
