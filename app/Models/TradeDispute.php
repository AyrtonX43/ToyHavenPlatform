<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TradeDispute extends Model
{
    protected $fillable = [
        'trade_id',
        'reporter_id',
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

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function getTypeLabel(): string
    {
        return match ($this->type) {
            'not_received' => 'Not Received',
            'damaged' => 'Damaged Item',
            'wrong_item' => 'Wrong Item',
            'other' => 'Other Issue',
            default => 'Unknown',
        };
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'open' => 'Open',
            'investigating' => 'Under Investigation',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => 'Unknown',
        };
    }
}
