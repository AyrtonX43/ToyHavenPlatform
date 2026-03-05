<?php

namespace App\Notifications;

use App\Models\Trade;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TradeCompletedAdminNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Trade $trade) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $trade = $this->trade->load([
            'initiator', 'participant', 'tradeListing.images',
            'tradeOffer.offeredTradeListing.images', 'items', 'proofs',
        ]);

        return [
            'type' => 'trade_completed_admin',
            'title' => 'Trade #' . $trade->id . ' Completed',
            'message' => 'Trade between ' . $trade->initiator?->name . ' and ' . $trade->participant?->name . ' has been completed. Both parties submitted proof.',
            'trade_id' => $trade->id,
            'action_url' => route('admin.trades.show', $trade->id),
            'initiator' => $trade->initiator ? ['id' => $trade->initiator->id, 'name' => $trade->initiator->name, 'email' => $trade->initiator->email] : null,
            'participant' => $trade->participant ? ['id' => $trade->participant->id, 'name' => $trade->participant->name, 'email' => $trade->participant->email] : null,
            'listings' => [
                'primary' => $trade->tradeListing ? ['id' => $trade->tradeListing->id, 'title' => $trade->tradeListing->title] : null,
                'offered' => $trade->tradeOffer?->offeredTradeListing ? ['id' => $trade->tradeOffer->offeredTradeListing->id, 'title' => $trade->tradeOffer->offeredTradeListing->title] : null,
            ],
        ];
    }
}
