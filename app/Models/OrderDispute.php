<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDispute extends Model
{
    protected $fillable = [
        'order_id',
        'user_id',
        'seller_id',
        'type',
        'description',
        'evidence_images',
        'status',
        'assigned_to',
        'resolution_notes',
        'resolution_type',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'evidence_images' => 'array',
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

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isInvestigating(): bool
    {
        return $this->status === 'investigating';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function getTypeLabel(): string
    {
        return match($this->type) {
            'not_received' => 'Not Received',
            'damaged' => 'Damaged Item',
            'wrong_item' => 'Wrong Item',
            'incomplete' => 'Incomplete Order',
            'other' => 'Other Issue',
            default => 'Unknown',
        };
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'open' => 'Open',
            'investigating' => 'Under Investigation',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => 'Unknown',
        };
    }
}
