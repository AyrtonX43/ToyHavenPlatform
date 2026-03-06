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
        'start_at',
        'end_at',
        'status',
        'winner_id',
        'winning_amount',
        'bids_count',
        'terms_accepted_at',
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

    public const STATUS_DRAFT = 'draft';
    public const STATUS_PENDING_APPROVAL = 'pending_approval';
    public const STATUS_ACTIVE = 'active';
    public const STATUS_ENDED = 'ended';
    public const STATUS_CANCELLED = 'cancelled';

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
        return $this->belongsTo(UserProduct::class, 'user_product_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function bids(): HasMany
    {
        return $this->hasMany(AuctionBid::class)->orderByDesc('amount');
    }

    public function images(): HasMany
    {
        return $this->hasMany(AuctionImage::class);
    }

    public function auctionPayments(): HasMany
    {
        return $this->hasMany(AuctionPayment::class);
    }

    public function currentWinningBid(): ?AuctionBid
    {
        return $this->bids()->where('is_winning', true)->first();
    }

    public function currentPrice(): float
    {
        $winning = $this->currentWinningBid();
        return $winning ? (float) $winning->amount : (float) $this->starting_bid;
    }

    public function nextMinBid(): float
    {
        return $this->currentPrice() + (float) $this->bid_increment;
    }

    public function isDraft(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isPendingApproval(): bool
    {
        return $this->status === self::STATUS_PENDING_APPROVAL;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isEnded(): bool
    {
        return $this->status === self::STATUS_ENDED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function hasEnded(): bool
    {
        return $this->end_at && $this->end_at->isPast();
    }

    public function canBid(): bool
    {
        return $this->isActive() && ! $this->hasEnded();
    }

    public function primaryImage(): ?AuctionImage
    {
        return $this->images()->where('is_primary', true)->first()
            ?? $this->images()->first();
    }
}
