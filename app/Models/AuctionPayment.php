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

    public function isOverdue(): bool
    {
        return $this->payment_deadline && $this->payment_deadline->isPast() && $this->isPending();
    }
}
