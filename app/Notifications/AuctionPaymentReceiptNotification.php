<?php

namespace App\Notifications;

use App\Models\AuctionPayment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionPaymentReceiptNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public AuctionPayment $auctionPayment
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'auction_payment_receipt',
            'title' => 'Official Receipt Sent',
            'auction_payment_id' => $this->auctionPayment->id,
            'auction_id' => $this->auctionPayment->auction_id,
            'receipt_number' => $this->auctionPayment->receipt_number,
            'amount' => (float) $this->auctionPayment->total_amount,
            'message' => 'Your official receipt for "' . $this->auctionPayment->auction->title . '" (#' . ($this->auctionPayment->receipt_number ?? '') . ') has been sent to your email.',
            'action_url' => route('auctions.wins.show', $this->auctionPayment),
        ];
    }
}
