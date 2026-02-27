<?php

namespace App\Notifications;

use App\Models\Auction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AuctionLostNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Auction $auction,
        public array $similarAuctionIds = []
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('Auction Ended: ' . $this->auction->title)
            ->greeting('Better luck next time!')
            ->line("The auction for **{$this->auction->title}** has ended and another bidder won.")
            ->line('Don\'t worry - you have options:')
            ->line('- **Keep your deposit** in your ToyHaven Wallet for the next auction')
            ->line('- **Check similar listings** that might interest you');

        if (! empty($this->similarAuctionIds)) {
            $mail->action('View Similar Auctions', url('/auctions'));
        } else {
            $mail->action('Browse Auctions', url('/auctions'));
        }

        return $mail->line('Thank you for bidding on ToyHaven!');
    }

    public function toArray($notifiable): array
    {
        return [
            'type' => 'auction_lost',
            'auction_id' => $this->auction->id,
            'title' => $this->auction->title,
            'similar_auction_ids' => $this->similarAuctionIds,
            'message' => "You didn't win {$this->auction->title}. Check out similar listings!",
        ];
    }
}
