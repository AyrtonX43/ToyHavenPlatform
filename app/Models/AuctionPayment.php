<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionPayment extends Model
{
    protected $fillable = [
        'auction_id',
        'winner_id',
        'seller_user_id',
        'bid_amount',
        'buyer_premium',
        'total_amount',
        'platform_fee',
        'seller_payout',
        'payment_status',
        'escrow_status',
        'payment_link',
        'paymongo_payment_intent_id',
        'tracking_number',
        'delivery_status',
        'payment_deadline',
        'paid_at',
        'shipped_at',
        'delivered_at',
        'confirmed_at',
        'released_at',
        'is_second_chance',
    ];

    protected $casts = [
        'bid_amount' => 'decimal:2',
        'buyer_premium' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'seller_payout' => 'decimal:2',
        'payment_deadline' => 'datetime',
        'paid_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'released_at' => 'datetime',
        'is_second_chance' => 'boolean',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_user_id');
    }

    public function isPastDeadline(): bool
    {
        return $this->payment_deadline && $this->payment_deadline->isPast();
    }

    public function isEscrowHeld(): bool
    {
        return $this->escrow_status === 'held';
    }

    public function canRelease(): bool
    {
        if ($this->escrow_status !== 'held') {
            return false;
        }

        if ($this->confirmed_at) {
            return true;
        }

        if ($this->delivered_at && $this->delivered_at->addDays(3)->isPast()) {
            return true;
        }

        return false;
    }
}
