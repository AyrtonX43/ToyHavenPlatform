<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionReview extends Model
{
    protected $fillable = [
        'auction_payment_id',
        'user_id',
        'auction_id',
        'auction_seller_profile_id',
        'rating',
        'feedback',
        'for_listing',
    ];

    protected $casts = [
        'for_listing' => 'boolean',
    ];

    public function auctionPayment(): BelongsTo
    {
        return $this->belongsTo(AuctionPayment::class, 'auction_payment_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function auctionSellerProfile(): BelongsTo
    {
        return $this->belongsTo(AuctionSellerProfile::class, 'auction_seller_profile_id');
    }
}
