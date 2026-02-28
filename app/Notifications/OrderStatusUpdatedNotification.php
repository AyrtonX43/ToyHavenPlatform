<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;
    protected string $oldStatus;
    protected string $newStatus;

    public function __construct(Order $order, string $oldStatus, string $newStatus)
    {
        $this->order = $order;
        $this->oldStatus = $oldStatus;
        $this->newStatus = $newStatus;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $statusLabel = $this->order->getStatusLabel();
        
        $message = (new MailMessage)
            ->subject('Order Status Update - ' . $this->order->order_number)
            ->line('Your order status has been updated.')
            ->line('Order Number: ' . $this->order->order_number)
            ->line('Status: ' . $statusLabel);

        if ($this->newStatus === 'shipped' && $this->order->tracking_number) {
            $message->line('Tracking Number: ' . $this->order->tracking_number);
            if ($this->order->courier_name) {
                $message->line('Courier: ' . $this->order->courier_name);
            }
        }

        if ($this->newStatus === 'delivered') {
            $message->line('Your order has been delivered! Please confirm receipt in your orders page.');
        }

        $message->action('View Order', route('orders.show', $this->order->id))
            ->line('Thank you for shopping at ToyHaven!');

        return $message;
    }

    public function toArray($notifiable): array
    {
        $message = 'Order #' . $this->order->order_number . ' status updated to ' . $this->order->getStatusLabel();
        
        if ($this->newStatus === 'delivered') {
            $message .= '. Please confirm receipt.';
        }

        return [
            'type' => 'order_status',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'status' => $this->newStatus,
            'message' => $message,
        ];
    }
}
