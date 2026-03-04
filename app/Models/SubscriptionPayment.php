<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionPayment extends Model
{
    protected $fillable = [
        'subscription_id',
        'amount',
        'status',
        'paid_at',
        'receipt_number',
        'receipt_path',
        'receipt_generated_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'paid_at' => 'datetime',
        'receipt_generated_at' => 'datetime',
    ];

    public function hasReceipt(): bool
    {
        return ! empty($this->receipt_path);
    }

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }
}
