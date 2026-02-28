<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderReceipt extends Model
{
    protected $fillable = [
        'order_id',
        'receipt_number',
        'proof_photo_path',
        'delivery_notes',
        'confirmed_at',
    ];

    protected $casts = [
        'confirmed_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public static function generateReceiptNumber(): string
    {
        return 'RCP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function getProofPhotoUrlAttribute(): ?string
    {
        return $this->proof_photo_path ? asset('storage/' . $this->proof_photo_path) : null;
    }
}
