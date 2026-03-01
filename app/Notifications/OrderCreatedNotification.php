<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCreatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->load(['items', 'seller']);
        
        return (new MailMessage)
            ->subject('Order Confirmed - #' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your order has been confirmed and is being prepared for shipment.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Estimated Delivery:** ' . $this->order->estimated_delivery_date?->format('F d, Y'))
            ->action('Track Your Order', route('orders.show', $this->order->id))
            ->line('Thank you for your purchase!');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'order_created',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total,
            'message' => 'Your order #' . $this->order->order_number . ' has been confirmed.',
            'action_url' => route('orders.show', $this->order->id),
            'action_text' => 'View Order',
        ];
    }
}
