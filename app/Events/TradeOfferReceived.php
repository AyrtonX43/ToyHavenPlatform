<?php

namespace App\Events;

use App\Models\TradeOffer;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TradeOfferReceived implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public TradeOffer $offer
    ) {
        $this->offer->load(['tradeListing', 'offerer']);
    }

    public function broadcastOn(): array
    {
        $recipientId = $this->offer->tradeListing->user_id;
        return [
            new PrivateChannel('App.Models.User.' . $recipientId),
        ];
    }

    public function broadcastAs(): string
    {
        return 'TradeOfferReceived';
    }

    public function broadcastWith(): array
    {
        $listing = $this->offer->tradeListing;
        return [
            'offer_id' => $this->offer->id,
            'listing_id' => $listing->id,
            'listing_title' => $listing->title,
            'offerer_name' => $this->offer->offerer->name ?? 'Someone',
            'message' => $this->offer->offerer->name . ' made an offer on "' . $listing->title . '". Accept or reject to continue.',
            'action_url' => route('trading.offers.received'),
        ];
    }
}
