<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeListingImage extends Model
{
    protected $fillable = [
        'trade_listing_id',
        'image_path',
        'display_order',
        'is_thumbnail',
    ];

    protected $casts = [
        'display_order' => 'integer',
        'is_thumbnail' => 'boolean',
    ];

    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }
}
