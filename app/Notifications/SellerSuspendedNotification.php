<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SellerSuspendedNotification extends Notification
{
    use Queueable;

    protected $reason;
    protected $reportId;
    protected $businessName;

    public function __construct($reason, $reportId, $businessName)
    {
        $this->reason = $reason;
        $this->reportId = $reportId;
        $this->businessName = $businessName;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject('Business Account Suspended - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',')
            ->line('We are writing to inform you that your business account **' . $this->businessName . '** has been temporarily suspended from the ToyHaven Platform.')
            ->line('')
            ->line('**Reason for suspension:**')
            ->line($this->reason);

        if ($this->reportId) {
            $message->line('')
                ->line('This action was taken due to a report filed against your business account (Report ID: #' . $this->reportId . ').');
        }

        $message->line('')
            ->line('**During this suspension:**')
            ->line('• Your business account access is temporarily restricted')
            ->line('• You cannot create new product listings')
            ->line('• Your products are hidden from the marketplace')
            ->line('• Existing orders will continue to be processed')
            ->line('• You cannot receive new orders')
            ->line('')
            ->line('If you believe this suspension was made in error, please contact our support team for assistance.')
            ->action('Contact Support', url('/contact'))
            ->line('Thank you for your understanding.');

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'seller_suspended',
            'title' => 'Business Account Suspended',
            'message' => 'Your business account "' . $this->businessName . '" has been suspended.',
            'business_name' => $this->businessName,
            'reason' => $this->reason,
            'report_id' => $this->reportId,
            'action_url' => route('seller.dashboard'),
        ];
    }
}
