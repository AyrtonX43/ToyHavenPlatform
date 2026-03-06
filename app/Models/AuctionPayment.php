<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionPayment extends Model
{
    protected $fillable = [
        'auction_id',
        'winner_id',
        'amount',
        'payment_method',
        'payment_reference',
        'status',
        'paid_at',
        'payment_deadline',
        'receipt_path',
        'delivery_status',
        'confirmed_at',
        'is_second_chance',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'payment_deadline' => 'datetime',
            'confirmed_at' => 'datetime',
            'is_second_chance' => 'boolean',
        ];
    }

    public const STATUS_PENDING = 'pending';
    public const STATUS_PAID = 'paid';
    public const STATUS_HELD = 'held';
    public const STATUS_RELEASED = 'released';
    public const STATUS_REFUNDED = 'refunded';

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function sellerUser(): ?User
    {
        return $this->auction?->user;
    }

    public function isPaid(): bool
    {
        return in_array($this->status, [self::STATUS_PAID, self::STATUS_HELD, self::STATUS_RELEASED]);
    }

    public function canRelease(): bool
    {
        return $this->status === self::STATUS_HELD && $this->delivery_status === 'confirmed';
    }
}
