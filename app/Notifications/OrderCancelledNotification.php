<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderCancelledNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['user']);
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Order Cancelled - ' . $this->order->order_number)
            ->line('Order #' . $this->order->order_number . ' has been cancelled.')
            ->line('Customer: ' . $this->order->user->name)
            ->line('Reason: ' . ($this->order->cancellation_reason ?? 'Not specified'))
            ->line('Cancelled At: ' . $this->order->cancelled_at->format('M d, Y h:i A'))
            ->action('View Order', route('seller.orders.show', $this->order->id))
            ->line('Product stock has been restored.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_cancelled',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Order #' . $this->order->order_number . ' was cancelled by ' . $this->order->user->name,
        ];
    }
}
