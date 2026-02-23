<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order->load(['user', 'items']);
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $buyerName = $this->order->user->name ?? 'A customer';
        $itemCount = $this->order->items->count();

        return (new MailMessage)
            ->subject('New Order Received - ' . $this->order->order_number)
            ->line("{$buyerName} has purchased {$itemCount} item(s) from your store.")
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Total: ₱' . number_format($this->order->total, 2))
            ->action('View Order', route('seller.orders.show', $this->order->id))
            ->line('Thank you for selling on ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_status',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'New order #' . $this->order->order_number . ' - Payment received. ' . $this->order->user->name . ' purchased from your store.',
        ];
    }
}
