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
        'description',
        'features',
        'sort_order',
        'is_active',
        'can_register_individual_seller',
        'can_register_business_seller',
        'has_analytics_dashboard',
        'can_register_individual_auction_seller',
        'can_register_business_auction_seller',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'can_register_individual_seller' => 'boolean',
            'can_register_business_seller' => 'boolean',
            'has_analytics_dashboard' => 'boolean',
            'can_register_individual_auction_seller' => 'boolean',
            'can_register_business_auction_seller' => 'boolean',
        ];
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function planTerms(): HasMany
    {
        return $this->hasMany(PlanTerms::class);
    }

    public function latestTerms(): ?PlanTerms
    {
        return $this->planTerms()->orderByDesc('effective_at')->first();
    }

}
