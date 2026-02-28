<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderReceiptConfirmedNotification extends Notification implements ShouldQueue
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
            ->subject('Order Receipt Confirmed - ' . $this->order->order_number)
            ->line('The customer has confirmed receipt of order #' . $this->order->order_number)
            ->line('Customer: ' . $this->order->user->name)
            ->line('Confirmed At: ' . $this->order->receipt_confirmed_at->format('M d, Y h:i A'))
            ->action('View Order', route('seller.orders.show', $this->order->id))
            ->line('Thank you for selling on ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'order_receipt_confirmed',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'message' => 'Customer confirmed receipt of order #' . $this->order->order_number,
        ];
    }
}
