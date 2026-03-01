<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Storage;

class PaymentSuccessNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Order $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->order->load(['items', 'seller']);
        
        $message = (new MailMessage)
            ->subject('Payment Successful - Order #' . $this->order->order_number)
            ->greeting('Hello ' . $notifiable->name . '!')
            ->line('Your payment has been successfully processed.')
            ->line('**Order Number:** ' . $this->order->order_number)
            ->line('**Total Amount:** ₱' . number_format($this->order->total, 2))
            ->line('**Payment Method:** ' . strtoupper($this->order->payment_method))
            ->line('')
            ->line('**Order Items:**');

        foreach ($this->order->items as $item) {
            $message->line('• ' . $item->product_name . ' (x' . $item->quantity . ') - ₱' . number_format($item->subtotal, 2));
        }

        $message->line('')
            ->line('Your order is now being processed and will be shipped soon.')
            ->action('View Order Details', route('orders.show', $this->order->id))
            ->line('Thank you for shopping with ToyHaven!');

        if ($this->order->hasReceipt()) {
            $receiptPath = Storage::disk('public')->path($this->order->receipt_path);
            if (file_exists($receiptPath)) {
                $message->attach($receiptPath, [
                    'as' => 'Receipt_' . $this->order->receipt_number . '.pdf',
                    'mime' => 'application/pdf',
                ]);
            }
        }

        return $message;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'payment_success',
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
            'total_amount' => $this->order->total,
            'message' => 'Your payment of ₱' . number_format($this->order->total, 2) . ' has been successfully processed.',
            'action_url' => route('orders.show', $this->order->id),
            'action_text' => 'View Order',
        ];
    }
}
