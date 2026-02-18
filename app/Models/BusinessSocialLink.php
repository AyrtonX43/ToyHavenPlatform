<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessSocialLink extends Model
{
    protected $fillable = [
        'seller_id',
        'platform',
        'url',
        'display_name',
        'display_order',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    // Relationships
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    // Helper methods
    public function getPlatformIcon(): string
    {
        return match($this->platform) {
            'facebook' => 'bi-facebook',
            'instagram' => 'bi-instagram',
            'twitter' => 'bi-twitter',
            'youtube' => 'bi-youtube',
            'tiktok' => 'bi-tiktok',
            'linkedin' => 'bi-linkedin',
            'pinterest' => 'bi-pinterest',
            default => 'bi-link-45deg',
        };
    }
}
