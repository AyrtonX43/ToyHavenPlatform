<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'price',
        'interval',
        'interval_count',
        'description',
        'benefits',
        'features',
        'paymongo_plan_id',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'benefits' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    public function getBenefit(string $key, $default = null)
    {
        return data_get($this->benefits, $key, $default);
    }

    public function getBuyersPremiumRate(): float
    {
        return (float) $this->getBenefit('buyers_premium_rate', 5);
    }

    public function getEarlyAccessHours(): int
    {
        return (int) $this->getBenefit('early_access_hours', 0);
    }

    public function getToyshopDiscount(): float
    {
        return (float) $this->getBenefit('toyshop_discount', 0);
    }

    public function getFreeShippingMin(): ?float
    {
        $min = $this->getBenefit('free_shipping_min');
        return $min !== null ? (float) $min : null;
    }

    public function hasMembersOnlyAuctions(): bool
    {
        return (bool) $this->getBenefit('members_only_auctions', false);
    }

    public function hasPrioritySupport(): bool
    {
        return (bool) $this->getBenefit('priority_support', false);
    }

    public function getBadgeLabel(): string
    {
        return $this->getBenefit('badge_label', $this->name);
    }
}
