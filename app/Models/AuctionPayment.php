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
        'tracking_number',
        'shipped_at',
        'confirmed_at',
        'released_at',
        'is_second_chance',
        'dispute_reason',
        'dispute_status',
        'disputed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
            'payment_deadline' => 'datetime',
            'shipped_at' => 'datetime',
            'confirmed_at' => 'datetime',
            'released_at' => 'datetime',
            'disputed_at' => 'datetime',
            'is_second_chance' => 'boolean',
        ];
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return in_array($this->status, ['paid', 'held', 'released']);
    }

    public function isHeld(): bool
    {
        return $this->status === 'held';
    }

    public function isReleased(): bool
    {
        return $this->status === 'released';
    }

    public function isOverdue(): bool
    {
        return $this->payment_deadline && $this->payment_deadline->isPast() && $this->isPending();
    }

    public function isShipped(): bool
    {
        return in_array($this->delivery_status, ['shipped', 'delivered', 'confirmed']);
    }

    public function isDelivered(): bool
    {
        return in_array($this->delivery_status, ['delivered', 'confirmed']);
    }

    public function isDisputed(): bool
    {
        return $this->dispute_status === 'open';
    }

    public function canAutoConfirmDelivery(): bool
    {
        return $this->delivery_status === 'shipped'
            && $this->shipped_at
            && $this->shipped_at->addDays(7)->isPast()
            && ! $this->isDisputed();
    }

    public function canReleaseEscrow(): bool
    {
        return $this->isHeld()
            && $this->isDelivered()
            && $this->confirmed_at
            && $this->confirmed_at->addDays(3)->isPast()
            && ! $this->isDisputed();
    }
}
