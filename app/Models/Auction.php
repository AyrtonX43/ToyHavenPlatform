<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Auction extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'product_id',
        'user_product_id',
        'category_id',
        'title',
        'description',
        'box_condition',
        'authenticity_marks',
        'known_defects',
        'provenance',
        'verification_video_path',
        'starting_bid',
        'reserve_price',
        'bid_increment',
        'start_at',
        'end_at',
        'duration_minutes',
        'status',
        'auction_type',
        'is_members_only',
        'early_access_hours',
        'allowed_bidder_plans',
        'winner_id',
        'winning_amount',
        'bids_count',
        'views_count',
        'is_promoted',
        'promoted_until',
    ];

    protected $casts = [
        'starting_bid' => 'decimal:2',
        'reserve_price' => 'decimal:2',
        'bid_increment' => 'decimal:2',
        'winning_amount' => 'decimal:2',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
        'promoted_until' => 'datetime',
        'is_members_only' => 'boolean',
        'is_promoted' => 'boolean',
        'allowed_bidder_plans' => 'array',
    ];

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

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'auction_category')->withTimestamps();
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
        return $this->hasMany(AuctionImage::class)->orderBy('display_order');
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('status', 'live')
            ->where(function ($q) {
                $q->whereNull('end_at')->orWhere('end_at', '>', now());
            });
    }

    public function scopeEnded(Builder $query): Builder
    {
        return $query->where('status', 'ended')
            ->orWhere(function ($q) {
                $q->where('status', 'live')->whereNotNull('end_at')->where('end_at', '<=', now());
            });
    }

    public function getCurrentHighBid(): ?AuctionBid
    {
        return $this->bids()->where('is_winning', true)->first()
            ?? $this->bids()->orderByDesc('amount')->first();
    }

    public function getCurrentPrice(): float
    {
        $high = $this->getCurrentHighBid();
        return $high ? (float) $high->amount : (float) $this->starting_bid;
    }

    public function getMinNextBid(): float
    {
        return $this->getCurrentPrice() + (float) $this->bid_increment;
    }

    public function isEnded(): bool
    {
        return $this->status === 'ended' || ($this->end_at && $this->end_at->isPast());
    }

    public function canBid(): bool
    {
        return $this->status === 'live' && ! $this->isEnded();
    }

    public function getItem()
    {
        if ($this->product_id) {
            return $this->product;
        }
        return $this->userProduct;
    }

    public function getPrimaryImageUrl(): ?string
    {
        $img = $this->images()->first();
        if ($img) {
            return asset('storage/'.$img->path);
        }
        $item = $this->getItem();
        if ($item && method_exists($item, 'images')) {
            $first = $item->images()->first();
            return $first ? asset('storage/'.$first->path) : null;
        }
        if ($item && isset($item->image_path)) {
            return asset('storage/'.$item->image_path);
        }
        return null;
    }

    public static function buyerPremiumRate(?User $user): float
    {
        if (! $user) {
            return 5.0;
        }
        $plan = $user->currentPlan();
        return $plan ? $plan->getBuyersPremiumRate() : 5.0;
    }

    public function isVisibleToUser(?User $user): bool
    {
        if ($this->is_members_only && ! $user?->hasActiveMembership()) {
            return false;
        }
        return true;
    }

    public function canUserBid(User $user): bool
    {
        if (! $user->hasActiveMembership() && ! $user->isAdmin()) {
            return false;
        }
        if ($this->user_id === $user->id) {
            return false;
        }
        if (! $this->isUserPlanAllowed($user)) {
            return false;
        }
        return $this->canBid();
    }

    public function isUserPlanAllowed(User $user): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        $allowed = $this->allowed_bidder_plans;
        if (empty($allowed) || in_array('all', $allowed)) {
            return true;
        }

        $plan = $user->currentPlan();
        if (! $plan) {
            return false;
        }

        return in_array($plan->slug, $allowed);
    }

    public function isLiveEvent(): bool
    {
        return $this->auction_type === 'live_event';
    }

    public function isTimed(): bool
    {
        return $this->auction_type === 'timed';
    }

    public function isCurrentlyPromoted(): bool
    {
        return $this->is_promoted && $this->promoted_until && $this->promoted_until->isFuture();
    }

    public function auctionPayment()
    {
        return $this->hasOne(\App\Models\AuctionPayment::class);
    }

    public function secondChances()
    {
        return $this->hasMany(\App\Models\AuctionSecondChance::class)->orderBy('queue_position');
    }

    public static function boxConditionLabel(string $condition): string
    {
        return match ($condition) {
            'sealed' => 'Sealed / Mint in Box',
            'opened_complete' => 'Opened - Complete',
            'opened_incomplete' => 'Opened - Incomplete',
            'no_box' => 'No Box / Loose',
            default => ucfirst($condition),
        };
    }

    /**
     * Check if user can see this auction early (based on plan early_access_hours)
     */
    public function canUserSeeEarly(?User $user): bool
    {
        if (! $this->start_at) {
            return true;
        }
        $hours = 0;
        if ($user && $user->hasActiveMembership()) {
            $plan = $user->currentPlan();
            if ($plan) {
                $hours = $plan->getEarlyAccessHours();
            }
        }
        return $this->start_at->copy()->subHours($hours)->lte(now());
    }
}
