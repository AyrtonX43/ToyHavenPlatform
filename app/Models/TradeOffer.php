<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function getOfferedItem()
    {
        if ($this->offered_product_id) {
            return $this->offeredProduct;
        }
        if ($this->offered_user_product_id) {
            return $this->offeredUserProduct;
        }
        return null;
    }
}
