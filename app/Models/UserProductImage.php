<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserProductImage extends Model
{
    protected $fillable = [
        'user_product_id',
        'image_path',
        'is_primary',
        'display_order',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    // Relationships
    public function userProduct(): BelongsTo
    {
        return $this->belongsTo(UserProduct::class);
    }
}
