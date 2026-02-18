<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class TradeListing extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'user_product_id',
        'category_id',
        'title',
        'brand',
        'description',
        'condition',
        'location',
        'location_lat',
        'location_lng',
        'meet_up_references',
        'image_path',
        'trade_type',
        'desired_items',
        'cash_difference',
        'status',
        'expires_at',
        'views_count',
        'offers_count',
    ];

    protected $casts = [
        'desired_items' => 'array',
        'cash_difference' => 'decimal:2',
        'expires_at' => 'datetime',
        'views_count' => 'integer',
        'offers_count' => 'integer',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function userProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(TradeListingImage::class)->orderBy('display_order');
    }

    public function offers(): HasMany
    {
        return $this->hasMany(TradeOffer::class);
    }

    public function activeOffers(): HasMany
    {
        return $this->hasMany(TradeOffer::class)->where('status', 'pending');
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    // Scopes
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            });
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query->where('status', 'active')
            ->where('expires_at', '<=', now());
    }

    public function scopeByType(Builder $query, string $type): Builder
    {
        return $query->where('trade_type', $type);
    }

    // Helper methods
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function canAcceptOffers(): bool
    {
        return $this->status === 'active' && !$this->isExpired();
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'active' => 'Active',
            'pending_approval' => 'Pending Review',
            'rejected' => 'Rejected',
            'pending_trade' => 'Trade in Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            'expired' => 'Expired',
            default => 'Unknown',
        };
    }

    public function getItem()
    {
        if ($this->product_id) {
            return $this->product;
        }
        return $this->userProduct;
    }
}
