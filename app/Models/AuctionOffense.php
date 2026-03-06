<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionOffense extends Model
{
    protected $fillable = [
        'user_id',
        'offense_type',
        'occurrence',
        'suspended_until',
    ];

    protected function casts(): array
    {
        return [
            'suspended_until' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
