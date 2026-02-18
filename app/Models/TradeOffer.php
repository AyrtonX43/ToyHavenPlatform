<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class TradeOffer extends Model
{
    protected $fillable = [
        'trade_listing_id',
        'offerer_id',
        'offerer_seller_id',
        'offered_product_id',
        'offered_user_product_id',
        'cash_amount',
        'message',
        'status',
        'counter_offer_id',
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
    ];

    // Relationships
    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }

    public function offerer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'offerer_id');
    }

    public function offererSeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'offerer_seller_id');
    }

    public function offeredProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'offered_product_id');
    }

    public function offeredUserProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class, 'offered_user_product_id');
    }

    public function counterOffer(): BelongsTo
    {
        return $this->belongsTo(TradeOffer::class, 'counter_offer_id');
    }

    public function trade(): HasOne
    {
        return $this->hasOne(Trade::class);
    }

    // Helper methods
    public function canBeAccepted(): bool
    {
        return $this->status === 'pending' && $this->tradeListing->canAcceptOffers();
    }

    public function canBeWithdrawn(): bool
    {
        return in_array($this->status, ['pending', 'counter_offered']);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'accepted' => 'Accepted',
            'rejected' => 'Rejected',
            'counter_offered' => 'Counter Offered',
            'withdrawn' => 'Withdrawn',
            default => 'Unknown',
        };
    }

    public function getOfferedItem()
    {
        if ($this->offered_product_id) {
            return $this->offeredProduct;
        }
        return $this->offeredUserProduct;
    }
}
