<?php

use App\Models\Auction;
use App\Models\Conversation;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    $conversation = Conversation::find($conversationId);
    if (! $conversation) {
        return false;
    }
    return $conversation->isParticipant($user->id);
});

Broadcast::channel('auction.{auctionId}', function ($user, $auctionId) {
    $auction = Auction::find($auctionId);
    if (! $auction) {
        return false;
    }

    return [
        'id' => $user->id,
        'name' => 'Bidder',
    ];
});

Broadcast::channel('auction-user.{userId}', function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
