<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class UserProduct extends Model
{
    protected $fillable = [
        'user_id',
        'category_id',
        'name',
        'description',
        'brand',
        'condition',
        'estimated_value',
        'status',
    ];

    protected $casts = [
        'estimated_value' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(UserProductImage::class)->orderBy('display_order');
    }

    public function primaryImage(): BelongsTo
    {
        return $this->belongsTo(UserProductImage::class)->where('is_primary', true);
    }

    public function tradeListings(): HasMany
    {
        return $this->hasMany(TradeListing::class, 'user_product_id');
    }

    // Helper methods
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }

    public function isInTrade(): bool
    {
        return $this->status === 'in_trade';
    }
}
