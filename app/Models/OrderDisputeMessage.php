<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDisputeMessage extends Model
{
    protected $fillable = [
        'order_dispute_id',
        'user_id',
        'message',
        'attachments',
        'is_internal',
    ];

    protected $casts = [
        'attachments' => 'array',
        'is_internal' => 'boolean',
    ];

    public function dispute(): BelongsTo
    {
        return $this->belongsTo(OrderDispute::class, 'order_dispute_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isSentByUser(): bool
    {
        return $this->user_id === $this->dispute->user_id;
    }

    public function isSentBySeller(): bool
    {
        return $this->user_id === $this->dispute->seller->user_id;
    }

    public function isSentByModerator(): bool
    {
        return $this->user_id === $this->dispute->moderator_id;
    }
}
