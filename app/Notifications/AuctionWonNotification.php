<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionWonNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Auction $auction
    ) {}

    public function via($notifiable)
    {
        return ['database', 'mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('You Won an Auction!')
            ->line('Congratulations! You won the auction: ' . $this->auction->title)
            ->line('Winning amount: ₱' . number_format($this->auction->winning_amount ?? $this->auction->getCurrentPrice(), 2))
            ->action('View Auction', route('auctions.show', $this->auction))
            ->line('Thank you for using ToyHaven Auctions!');
    }

    public function toArray($notifiable)
    {
        return [
            'type' => 'auction_won',
            'auction_id' => $this->auction->id,
            'auction_title' => $this->auction->title,
            'winning_amount' => (float) ($this->auction->winning_amount ?? $this->auction->getCurrentPrice()),
            'message' => 'You won the auction: ' . $this->auction->title,
        ];
    }
}
