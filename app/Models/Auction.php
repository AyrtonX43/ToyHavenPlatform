<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    protected $fillable = [
        'auction_seller_profile_id',
        'category_id',
        'title',
        'description',
        'starting_bid',
        'reserve_price',
        'bid_increment',
        'allowed_bidder_plan_ids',
        'status',
        'start_at',
        'end_at',
        'winner_id',
        'winning_amount',
        'bids_count',
        'views_count',
        'rejection_reason',
    ];

    protected $casts = [
        'starting_bid' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'winning_amount' => 'decimal:2',
        'allowed_bidder_plan_ids' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function auctionSellerProfile(): BelongsTo
    {
        return $this->belongsTo(AuctionSellerProfile::class, 'auction_seller_profile_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AuctionImage::class)->orderBy('display_order');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(AuctionBid::class)->orderByDesc('amount');
    }

    public function auctionPayment()
    {
        return $this->hasOne(AuctionPayment::class);
    }

    public function secondChances(): HasMany
    {
        return $this->hasMany(AuctionSecondChance::class)->orderBy('queue_position');
    }

    public function savedBy(): HasMany
    {
        return $this->hasMany(SavedAuction::class);
    }

    public function scopeLive($query)
    {
        return $query->where('status', 'live');
    }

    public function scopeEnded($query)
    {
        return $query->where('status', 'ended');
    }

    public function isLive(): bool
    {
        return $this->status === 'live';
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended';
    }

    public function getSellerUser(): ?User
    {
        return $this->auctionSellerProfile?->user;
    }
}
