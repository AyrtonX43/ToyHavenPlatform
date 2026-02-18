<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trade extends Model
{
    protected $fillable = [
        'trade_listing_id',
        'trade_offer_id',
        'initiator_id',
        'initiator_seller_id',
        'participant_id',
        'participant_seller_id',
        'cash_amount',
        'status',
        'initiator_shipping_address',
        'participant_shipping_address',
        'initiator_tracking_number',
        'participant_tracking_number',
        'initiator_shipped_at',
        'participant_shipped_at',
        'initiator_received_at',
        'participant_received_at',
        'completed_at',
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
        'initiator_shipping_address' => 'array',
        'participant_shipping_address' => 'array',
        'initiator_shipped_at' => 'datetime',
        'participant_shipped_at' => 'datetime',
        'initiator_received_at' => 'datetime',
        'participant_received_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // Relationships
    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }

    public function tradeOffer(): BelongsTo
    {
        return $this->belongsTo(TradeOffer::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function initiatorSeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'initiator_seller_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function participantSeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'participant_seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TradeItem::class);
    }

    public function initiatorItems(): HasMany
    {
        return $this->hasMany(TradeItem::class)->where('side', 'initiator');
    }

    public function participantItems(): HasMany
    {
        return $this->hasMany(TradeItem::class)->where('side', 'participant');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    // Helper methods
    public function canBeCompleted(): bool
    {
        return $this->initiator_received_at && $this->participant_received_at && $this->status !== 'completed';
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending_shipping' => 'Pending Shipping',
            'shipped' => 'Shipped',
            'received' => 'Received',
            'completed' => 'Completed',
            'disputed' => 'Disputed',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getProgressPercentage(): int
    {
        return match($this->status) {
            'pending_shipping' => 10,
            'shipped' => 50,
            'received' => 90,
            'completed' => 100,
            'disputed' => 0,
            'cancelled' => 0,
            default => 0,
        };
    }

    public function getOtherParty($userId)
    {
        if ($this->initiator_id === $userId) {
            return $this->participant;
        }
        return $this->initiator;
    }

    public function isInitiator($userId): bool
    {
        return $this->initiator_id === $userId;
    }

    public function isParticipant($userId): bool
    {
        return $this->participant_id === $userId;
    }
}
