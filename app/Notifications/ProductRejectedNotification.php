<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductRejectedNotification extends Notification
{
    use Queueable;

    protected $productName;
    protected $rejectionReason;
    protected $productSku;

    /**
     * Create a new notification instance.
     */
    public function __construct($productName, $rejectionReason, $productSku = null)
    {
        $this->productName = $productName;
        $this->rejectionReason = $rejectionReason;
        $this->productSku = $productSku;
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
            ->subject('Product Rejected - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',');

        $message->line('We regret to inform you that your product **' . $this->productName . '**' . 
            ($this->productSku ? ' (SKU: ' . $this->productSku . ')' : '') . 
            ' has been rejected on the ToyHaven Platform.');

        if ($this->rejectionReason) {
            $message->line('**Reason for rejection:**')
                    ->line($this->rejectionReason);
        }

        $message->line('You may:')
            ->line('• Review the rejection reason and address any issues')
            ->line('• Edit and resubmit your product for review')
            ->line('• Contact our support team if you have questions')
            ->line('')
            ->line('If you believe this decision was made in error, please contact our support team for assistance.')
            ->action('View Your Products', url('/seller/products'))
            ->line('Thank you for using ToyHaven Platform.');

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
            'product_name' => $this->productName,
            'rejection_reason' => $this->rejectionReason,
            'product_sku' => $this->productSku,
        ];
    }
}
