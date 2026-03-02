<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ProductApprovedNotification extends Notification
{
    use Queueable;

    protected $productName;
    protected $productSku;
    protected $productId;

    /**
     * Create a new notification instance.
     */
    public function __construct($productName, $productSku = null, $productId = null)
    {
        $this->productName = $productName;
        $this->productSku = $productSku;
        $this->productId = $productId;
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
            ->subject('Product Approved - ToyHaven Platform')
            ->greeting('Hello ' . $notifiable->name . ',');

        $message->line('Great news! Your product **' . $this->productName . '**' . 
            ($this->productSku ? ' (SKU: ' . $this->productSku . ')' : '') . 
            ' has been approved and is now live on the ToyHaven Platform.');

        $message->line('Your product is now visible to customers and available for purchase.')
            ->line('**What happens next?**')
            ->line('• Customers can now view and purchase your product')
            ->line('• You will receive notifications when orders are placed')
            ->line('• Keep your inventory updated to avoid overselling')
            ->line('• Monitor your product performance in your dashboard')
            ->line('')
            ->action('View Your Product', url('/seller/products'))
            ->line('Thank you for being a valued seller on ToyHaven Platform!');

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
            'type' => 'product_approved',
            'product_name' => $this->productName,
            'product_sku' => $this->productSku,
            'product_id' => $this->productId,
            'title' => 'Product Approved',
            'message' => 'Your product "' . $this->productName . '" has been approved and is now live.',
            'action_url' => url('/seller/products'),
            'action_text' => 'View Products',
        ];
    }
}
