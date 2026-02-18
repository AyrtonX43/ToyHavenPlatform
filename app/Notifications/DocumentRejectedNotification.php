<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentRejectedNotification extends Notification
{
    use Queueable;

    protected $documentType;
    protected $reason;
    protected $businessName;

    /**
     * Create a new notification instance.
     */
    public function __construct($documentType, $reason, $businessName = null)
    {
        $this->documentType = $documentType;
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
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $documentName = ucfirst(str_replace('_', ' ', $this->documentType));
        
        $message = (new MailMessage)
            ->subject('Verification Document Rejected - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',');

        if ($this->businessName) {
            $message->line('We regret to inform you that your **' . $documentName . '** document for your business account **' . $this->businessName . '** has been rejected on the ToyHaven Platform.');
        } else {
            $message->line('We regret to inform you that your **' . $documentName . '** document has been rejected on the ToyHaven Platform.');
        }

        $message->line('**Reason for rejection:**')
                ->line($this->reason)
                ->line('')
                ->line('**Important:** Your required verification document is invalid. Please:')
                ->line('• Review the rejection reason provided above')
                ->line('• Upload a new, valid document that meets our requirements')
                ->line('• Ensure the document is clear, valid, and matches your business information')
                ->line('')
                ->line('Your seller account verification cannot be completed until all required documents are approved.')
                ->action('View Your Documents', url('/seller/dashboard'))
                ->line('If you have any questions, please contact our support team.')
                ->line('Thank you for your cooperation.');

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
            'document_type' => $this->documentType,
            'reason' => $this->reason,
            'business_name' => $this->businessName,
        ];
    }
}
