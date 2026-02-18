<?php

namespace App\Notifications;

use App\Models\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeStatusUpdatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $trade;
    protected $status;

    public function __construct(Trade $trade, string $status)
    {
        $this->trade = $trade;
        $this->status = $status;
    }

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        $statusMessages = [
            'shipped' => 'Your trade partner has shipped their item!',
            'received' => 'Your trade partner has confirmed receipt of your item.',
            'completed' => 'Your trade has been completed successfully!',
        ];

        $message = $statusMessages[$this->status] ?? 'Your trade status has been updated.';

        return (new MailMessage)
            ->subject('Trade Status Updated')
            ->line($message)
            ->line('Trade: ' . $this->trade->tradeListing->title)
            ->action('View Trade', route('trading.trades.show', $this->trade->id));
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'trade_status_updated',
            'trade_id' => $this->trade->id,
            'status' => $this->status,
            'listing_title' => $this->trade->tradeListing->title,
            'message' => 'Trade status updated: ' . $this->trade->getStatusLabel(),
        ];
    }
}
