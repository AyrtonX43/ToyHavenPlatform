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
        'category_ids',
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
        'condition',
        'reserve_price',
        'min_watchers_to_approve',
        'auction_outcome',
    ];

    public const CONDITIONS = [
        'new' => 'New',
        'like_new' => 'Like New',
        'good' => 'Good',
        'fair' => 'Fair',
    ];

    protected function casts(): array
    {
        return [
            'category_ids' => 'array',
            'starting_bid' => 'decimal:2',
            'bid_increment' => 'decimal:2',
            'winning_amount' => 'decimal:2',
            'reserve_price' => 'decimal:2',
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

    public function categories()
    {
        $ids = $this->category_ids ?? [];
        if (empty($ids) && $this->category_id) {
            $ids = [$this->category_id];
        }
        if (empty($ids)) {
            return collect([]);
        }
        $cats = Category::whereIn('id', $ids)->get();

        return $cats->sortBy(fn ($c) => array_search($c->id, $ids))->values();
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

    public function images()
    {
        return $this->hasMany(AuctionImage::class)->orderBy('display_order');
    }

    public function savedBy()
    {
        return $this->belongsToMany(User::class, 'saved_auctions')->withTimestamps();
    }

    public function watchersCount(): int
    {
        return \Illuminate\Support\Facades\DB::table('saved_auctions')
            ->where('auction_id', $this->id)
            ->count();
    }

    public function meetsReserve(): bool
    {
        if ($this->reserve_price === null || $this->reserve_price <= 0) {
            return true;
        }

        return $this->winning_amount !== null && (float) $this->winning_amount >= (float) $this->reserve_price;
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
