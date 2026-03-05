<?php

namespace App\Policies;

use App\Models\TradeListing;
use App\Models\User;

class TradeListingPolicy
{
    /**
     * Only the owner can update. Admin and Moderator cannot edit listings.
     */
    public function update(User $user, TradeListing $listing): bool
    {
        return $listing->user_id === $user->id;
    }

    /**
     * Only the owner can delete. Admin and Moderator cannot delete listings.
     */
    public function delete(User $user, TradeListing $listing): bool
    {
        return $listing->user_id === $user->id;
    }

    /**
     * Owner can manage their own listing.
     */
    public function manage(User $user, TradeListing $listing): bool
    {
        return $listing->user_id === $user->id;
    }
}
