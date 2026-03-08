<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionReview extends Model
{
    protected $fillable = [
        'auction_payment_id',
        'winner_id',
        'auction_id',
        'seller_user_id',
        'rating',
        'feedback',
        'delivery_confirmed_at',
    ];

    protected function casts(): array
    {
        return [
            'delivery_confirmed_at' => 'datetime',
        ];
    }

    public function auctionPayment(): BelongsTo
    {
        return $this->belongsTo(AuctionPayment::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }
}
