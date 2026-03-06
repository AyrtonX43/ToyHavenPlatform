<?php

namespace App\Policies;

use App\Models\Auction;
use App\Models\User;

class AuctionPolicy
{
    public function update(User $user, Auction $auction): bool
    {
        return $auction->user_id === $user->id;
    }

    public function delete(User $user, Auction $auction): bool
    {
        return $auction->user_id === $user->id;
    }
}
