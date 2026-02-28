<?php

namespace App\Notifications;

use App\Models\OrderDisputeMessage;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeMessageNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected OrderDisputeMessage $message;

    public function __construct(OrderDisputeMessage $message)
    {
        $this->message = $message->load(['dispute.order', 'user']);
    }

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'dispute_message',
            'dispute_id' => $this->message->dispute->id,
            'dispute_number' => $this->message->dispute->dispute_number,
            'sender_name' => $this->message->user->name,
            'message' => 'New message in dispute #' . $this->message->dispute->dispute_number,
        ];
    }
}
