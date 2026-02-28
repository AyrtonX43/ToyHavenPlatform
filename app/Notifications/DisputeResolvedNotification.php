<?php

namespace App\Notifications;

use App\Models\OrderDispute;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DisputeResolvedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected OrderDispute $dispute;

    public function __construct(OrderDispute $dispute)
    {
        $this->dispute = $dispute->load(['order']);
    }

    public function via($notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $resolutionText = match($this->dispute->resolution) {
            'refund' => 'A full refund has been issued.',
            'replacement' => 'A replacement order will be sent.',
            'partial_refund' => 'A partial refund has been issued.',
            'no_action' => 'No action was taken based on the investigation.',
            default => 'The dispute has been resolved.',
        };

        return (new MailMessage)
            ->subject('Dispute Resolved - ' . $this->dispute->dispute_number)
            ->line('Your dispute has been resolved.')
            ->line('Dispute Number: ' . $this->dispute->dispute_number)
            ->line('Order Number: ' . $this->dispute->order->order_number)
            ->line('Resolution: ' . $resolutionText)
            ->line('Notes: ' . ($this->dispute->resolution_notes ?? 'N/A'))
            ->action('View Dispute', route('orders.disputes.show', $this->dispute->id))
            ->line('Thank you for your patience.');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'dispute_resolved',
            'dispute_id' => $this->dispute->id,
            'dispute_number' => $this->dispute->dispute_number,
            'resolution' => $this->dispute->resolution,
            'message' => 'Dispute #' . $this->dispute->dispute_number . ' has been resolved.',
        ];
    }
}
