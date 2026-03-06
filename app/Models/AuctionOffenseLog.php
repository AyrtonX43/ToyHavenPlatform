<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionOffenseLog extends Model
{
    protected $fillable = [
        'user_id',
        'auction_id',
        'auction_payment_id',
        'reason',
        'occurrence',
        'action_taken',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function auctionPayment(): BelongsTo
    {
        return $this->belongsTo(AuctionPayment::class);
    }
}
