<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryConfirmation extends Model
{
    protected $fillable = [
        'order_id',
        'proof_image_path',
        'proof_image_paths',
        'notes',
        'auto_confirmed',
        'confirmed_at',
    ];

    protected $casts = [
        'proof_image_paths' => 'array',
        'auto_confirmed' => 'boolean',
        'confirmed_at' => 'datetime',
    ];

    public function getProofImagesAttribute(): array
    {
        $paths = $this->proof_image_paths;
        if (is_array($paths) && count($paths) > 0) {
            return $paths;
        }
        return $this->proof_image_path ? [$this->proof_image_path] : [];
    }

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
