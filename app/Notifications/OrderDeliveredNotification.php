<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Delivered - ' . $this->order->order_number)
            ->line('Great news! Your order has been delivered.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Delivered At: ' . $this->order->delivered_at->format('M d, Y h:i A'))
            ->line('**Important:** Please confirm receipt of your order by uploading a photo proof within 3 days.')
            ->action('Confirm Receipt', route('orders.show', $this->order->id))
            ->line('If you did not receive the order, please report an issue.')
            ->line('Thank you for shopping at ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_delivered',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Order #' . $this->order->order_number . ' has been delivered. Please confirm receipt.',
            'action_required' => true,
        ];
    }
}
