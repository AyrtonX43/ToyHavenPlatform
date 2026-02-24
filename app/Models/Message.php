<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $appends = ['formatted_created_at'];

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'trade_listing_id',
        'message',
        'is_read',
        'delivered_at',
        'seen_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'delivered_at' => 'datetime',
        'seen_at' => 'datetime',
    ];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function isImage(): bool
    {
        return $this->attachments->contains(fn ($a) => str_starts_with($a->file_type ?? '', 'image/'));
    }

    public function isVideo(): bool
    {
        return $this->attachments->contains(fn ($a) => str_starts_with($a->file_type ?? '', 'video/'));
    }

    /**
     * Message time in app timezone, human-friendly (Today 3:45 PM, Yesterday 2:30 PM, or Feb 10, 3:45 PM).
     */
    public function getFormattedCreatedAtAttribute(): string
    {
        if (!$this->created_at) {
            return 'Just now';
        }
        
        $tz = config('app.timezone', 'Asia/Manila');
        $dt = $this->created_at->timezone($tz);
        $today = Carbon::now($tz)->startOfDay();
        $yesterday = $today->copy()->subDay();
        
        if ($dt->gte($today)) {
            return 'Today, ' . $dt->format('g:i A');
        }
        if ($dt->gte($yesterday) && $dt->lt($today)) {
            return 'Yesterday, ' . $dt->format('g:i A');
        }
        return $dt->format('M j, g:i A');
    }
    
    /**
     * Get the delivery status text.
     */
    public function getStatusTextAttribute(): string
    {
        if ($this->seen_at) {
            return 'Seen';
        }
        if ($this->delivered_at) {
            return 'Delivered';
        }
        return 'Sent';
    }
}
