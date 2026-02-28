<?php

namespace App\Notifications;

use App\Models\OrderDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeOpenedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected OrderDispute $dispute;
    protected string $recipientType;

    public function __construct(OrderDispute $dispute, string $recipientType = 'seller')
    {
        $this->dispute = $dispute->load(['order', 'user']);
        $this->recipientType = $recipientType;
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $message = (new MailMessage)
            ->subject('Dispute Opened - ' . $this->dispute->dispute_number);

        if ($this->recipientType === 'seller') {
            $message->line('A customer has opened a dispute for order #' . $this->dispute->order->order_number)
                ->line('Dispute Number: ' . $this->dispute->dispute_number)
                ->line('Reason: ' . $this->dispute->reason_label)
                ->line('Customer: ' . $this->dispute->user->name)
                ->action('View Dispute', route('seller.disputes.show', $this->dispute->id));
        } elseif ($this->recipientType === 'moderator') {
            $message->line('A new dispute has been opened and requires moderation.')
                ->line('Dispute Number: ' . $this->dispute->dispute_number)
                ->line('Order Number: ' . $this->dispute->order->order_number)
                ->line('Reason: ' . $this->dispute->reason_label)
                ->action('Review Dispute', route('moderator.disputes.show', $this->dispute->id));
        }

        return $message->line('Please respond as soon as possible.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'dispute_opened',
            'dispute_id' => $this->dispute->id,
            'dispute_number' => $this->dispute->dispute_number,
            'order_number' => $this->dispute->order->order_number,
            'message' => 'Dispute opened for order #' . $this->dispute->order->order_number . ' - ' . $this->dispute->reason_label,
            'action_required' => true,
        ];
    }
}
