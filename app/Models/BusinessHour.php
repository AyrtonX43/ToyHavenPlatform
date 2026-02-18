<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BusinessHour extends Model
{
    protected $fillable = [
        'seller_id',
        'day_of_week',
        'is_open',
        'open_time',
        'close_time',
        'is_24_hours',
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'is_24_hours' => 'boolean',
    ];

    // Relationships
    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    // Helper methods
    public function getDisplayTime(): string
    {
        if (!$this->is_open) {
            return 'Closed';
        }

        if ($this->is_24_hours) {
            return '24 Hours';
        }

        if ($this->open_time && $this->close_time) {
            return date('g:i A', strtotime($this->open_time)) . ' - ' . date('g:i A', strtotime($this->close_time));
        }

        return 'Not Set';
    }
}
