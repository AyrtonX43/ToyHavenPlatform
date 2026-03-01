<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryConfirmation extends Model
{
    protected $fillable = [
        'order_id',
        'proof_image_path',
        'notes',
        'auto_confirmed',
        'confirmed_at',
    ];

    protected $casts = [
        'auto_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function isAutoConfirmed(): bool
    {
        return $this->auto_confirmed;
    }

    public function isManuallyConfirmed(): bool
    {
        return !$this->auto_confirmed && $this->confirmed_at !== null;
    }
}
