<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionSecondChance extends Model
{
    protected $fillable = [
        'auction_id',
        'user_id',
        'bid_amount',
        'queue_position',
        'status',
        'payment_link',
        'offered_at',
        'deadline',
    ];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'offered_at' => 'datetime',
        'deadline' => 'datetime',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isExpired(): bool
    {
        return $this->deadline && $this->deadline->isPast();
    }
}
