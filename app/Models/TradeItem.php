<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeItem extends Model
{
    protected $fillable = [
        'trade_id',
        'product_id',
        'user_product_id',
        'user_id',
        'seller_id',
        'product_name',
        'product_description',
        'product_images',
        'product_condition',
        'estimated_value',
        'side',
    ];

    protected $casts = [
        'product_images' => 'array',
        'estimated_value' => 'decimal:2',
    ];

    // Relationships
    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function userProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function getItem()
    {
        if ($this->product_id) {
            return $this->product;
        }
        return $this->userProduct;
    }
}
