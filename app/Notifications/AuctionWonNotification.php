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

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $payment = $this->auction->auctionPayment;
        $amount = $payment ? number_format($payment->total_amount, 2) : number_format($this->auction->winning_amount, 2);

        return (new MailMessage)
            ->subject('You Won: ' . $this->auction->title)
            ->greeting('Congratulations!')
            ->line("You won the auction for **{$this->auction->title}**!")
            ->line("Total amount due: **₱{$amount}**")
            ->line('You have **24 hours** to complete payment. Failure to pay will result in a ban.')
            ->action('Pay Now', url('/auctions/payment/' . ($payment?->id ?? '')))
            ->line('Thank you for bidding on ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'auction_won',
            'auction_id' => $this->auction->id,
            'title' => $this->auction->title,
            'amount' => (float) $this->auction->winning_amount,
            'message' => "You won the auction for {$this->auction->title}! Pay within 24 hours.",
        ];
    }
}
