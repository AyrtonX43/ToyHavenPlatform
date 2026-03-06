<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionTerms extends Model
{
    protected $fillable = [
        'content',
        'version',
        'effective_at',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'effective_at' => 'datetime',
        ];
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public static function current(): ?self
    {
        return static::whereNotNull('effective_at')
            ->where('effective_at', '<=', now())
            ->orderByDesc('effective_at')
            ->first();
    }
}
