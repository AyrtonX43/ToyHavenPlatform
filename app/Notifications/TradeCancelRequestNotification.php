<?php

namespace App\Notifications;

use App\Models\Trade;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeCancelRequestNotification extends Notification
{
    public function __construct(
        public Trade $trade
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $this->trade->loadMissing(['initiator', 'participant']);
        $requester = $this->trade->getCancelRequester();
        $requesterName = $requester?->name ?? 'The other party';

        return (new MailMessage)
            ->subject('Trade Cancellation Request - ToyHaven')
            ->line($requesterName . ' wants to cancel the trade transaction with you.')
            ->line('You have 24 hours to respond. If you accept the cancellation or do not respond within 24 hours, the trade will be cancelled and both listings will return to active.')
            ->action('View Conversation', $this->trade->conversation ? route('trading.conversations.show', $this->trade->conversation) : route('trading.trades.index'));
    }

    public function toArray(object $notifiable): array
    {
        $this->trade->loadMissing(['initiator', 'participant', 'conversation']);
        $requester = $this->trade->getCancelRequester();
        $conversation = $this->trade->conversation;

        return [
            'type' => 'trade_cancel_request',
            'title' => 'Trade Cancellation Request',
            'message' => ($requester?->name ?? 'The other party') . ' wants to cancel the trade. You have 24 hours to respond.',
            'trade_id' => $this->trade->id,
            'conversation_id' => $conversation?->id,
            'action_url' => $conversation ? route('trading.conversations.show', $conversation) : route('trading.trades.index'),
        ];
    }
}
