<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OrderDispute extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'seller_id',
        'moderator_id',
        'dispute_number',
        'reason',
        'description',
        'evidence_photos',
        'status',
        'resolution',
        'resolution_notes',
        'resolved_at',
    ];

    protected $casts = [
        'evidence_photos' => 'array',
        'resolved_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function moderator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(OrderDisputeMessage::class)->orderBy('created_at');
    }

    public static function generateDisputeNumber(): string
    {
        return 'DSP-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
    }

    public function isOpen(): bool
    {
        return in_array($this->status, ['open', 'investigating']);
    }

    public function canBeResolved(): bool
    {
        return $this->status !== 'resolved' && $this->status !== 'closed';
    }

    public function getReasonLabelAttribute(): string
    {
        return match($this->reason) {
            'not_received' => 'Product Not Received',
            'damaged' => 'Product Damaged',
            'wrong_item' => 'Wrong Item Received',
            'incomplete' => 'Incomplete Order',
            'other' => 'Other Issue',
            default => 'Unknown',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'investigating' => 'Under Investigation',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => 'Unknown',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'open' => 'warning',
            'investigating' => 'info',
            'resolved' => 'success',
            'closed' => 'secondary',
            default => 'secondary',
        };
    }
}
