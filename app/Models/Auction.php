<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'seller_type',
        'product_id',
        'user_product_id',
        'category_id',
        'title',
        'description',
        'starting_bid',
        'bid_increment',
        'duration_hours',
        'start_at',
        'end_at',
        'status',
        'winner_id',
        'winning_amount',
        'bids_count',
        'terms_accepted_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'starting_bid' => 'decimal:2',
            'bid_increment' => 'decimal:2',
            'winning_amount' => 'decimal:2',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function payment()
    {
        return $this->hasOne(AuctionPayment::class);
    }

    public function bids()
    {
        return $this->hasMany(AuctionBid::class)->orderByDesc('amount');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPendingApproval(): bool
    {
        return $this->status === 'pending_approval';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function getCurrentBidAttribute(): ?float
    {
        return $this->winning_amount ?? (float) $this->starting_bid;
    }

    public function getNextMinBidAttribute(): float
    {
        return (float) $this->current_bid + (float) $this->bid_increment;
    }
}
