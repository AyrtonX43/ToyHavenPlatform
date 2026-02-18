<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'seller_id',
        'user_id',
        'category_id', // Primary category (for backward compatibility)
        'name',
        'slug',
        'description',
        'brand',
        'sku',
        'price',
        'base_price',
        'amazon_reference_price',
        'amazon_reference_image',
        'amazon_reference_url',
        'platform_fee_percentage',
        'tax_percentage',
        'final_price',
        'stock_quantity',
        'condition',
        'status',
        'rejection_reason',
        'is_tradeable',
        'trade_status',
        'views_count',
        'sales_count',
        'rating',
        'reviews_count',
        'specifications',
        'weight',
        'length',
        'width',
        'height',
        'video_url',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'base_price' => 'decimal:2',
        'amazon_reference_price' => 'decimal:2',
        'platform_fee_percentage' => 'decimal:2',
        'tax_percentage' => 'decimal:2',
        'final_price' => 'decimal:2',
        'rating' => 'decimal:2',
        'specifications' => 'array',
        'weight' => 'decimal:2',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'is_tradeable' => 'boolean',
    ];

    // Relationships
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class); // Primary category
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'product_categories')
                    ->withTimestamps();
    }

    public function variations(): HasMany
    {
        return $this->hasMany(ProductVariation::class)->orderBy('display_order');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('display_order');
    }

    public function primaryImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('status', 'approved');
    }

    public function cartItems(): HasMany
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function amazonPriceReference(): BelongsTo
    {
        return $this->belongsTo(AmazonPriceReference::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(ProductView::class);
    }

    // Helper methods
    public function isInStock(): bool
    {
        return $this->stock_quantity > 0 && $this->status === 'active';
    }

    public function getPriceDifference(): ?float
    {
        if ($this->amazon_reference_price) {
            return $this->amazon_reference_price - $this->price;
        }
        return null;
    }

    public function getPriceDifferencePercentage(): ?float
    {
        if ($this->amazon_reference_price && $this->amazon_reference_price > 0) {
            return (($this->amazon_reference_price - $this->price) / $this->amazon_reference_price) * 100;
        }
        return null;
    }

    public function isTradeable(): bool
    {
        return $this->is_tradeable && $this->trade_status === 'available_for_trade';
    }

    public function canBeTraded(): bool
    {
        return $this->isTradeable() && $this->status === 'active';
    }

    /**
     * HD version of Amazon reference image URL (for zoom/display).
     * Converts common Amazon size params to _AC_SL1500_ / _SL1500_.
     */
    public function getAmazonReferenceImageHdAttribute(): ?string
    {
        $url = $this->amazon_reference_image;
        if ($url === null || $url === '') {
            return $url;
        }
        $url = (string) $url;
        if (strpos($url, 'media-amazon.com') === false && strpos($url, 'images-amazon.com') === false) {
            return $url;
        }
        $url = preg_replace('/_S[LX]\d+_/', '_AC_SL1500_', $url);
        $url = preg_replace('/_SL\d+_/', '_SL1500_', $url);
        $url = preg_replace('/_AC_SL\d+_/', '_AC_SL1500_', $url);
        return $url;
    }
}
