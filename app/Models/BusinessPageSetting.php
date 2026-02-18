<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessPageSetting extends Model
{
    protected $fillable = [
        'seller_id',
        'page_name',
        'business_description',
        'logo_path',
        'banner_path',
        'primary_color',
        'secondary_color',
        'layout_type',
        'meta_title',
        'meta_description',
        'keywords',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'keywords' => 'array',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // Relationships
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    // Helper methods
    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => now(),
        ]);
    }

    public function unpublish(): void
    {
        $this->update([
            'is_published' => false,
            'published_at' => null,
        ]);
    }
}
