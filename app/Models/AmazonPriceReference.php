<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AmazonPriceReference extends Model
{
    protected $fillable = [
        'product_id',
        'amazon_url',
        'amazon_price',
        'currency',
        'last_updated',
    ];

    protected $casts = [
        'amazon_price' => 'decimal:2',
        'last_updated' => 'datetime',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
