<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Conversation extends Model
{
    protected $fillable = [
        'seller_id',
        'customer_id',
        'user1_id',
        'user2_id',
        'trade_id',
        'trade_listing_id',
        'subject',
        'last_message_at',
    ];

    protected $casts = [
        'last_message_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user1(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user1_id');
    }

    public function user2(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user2_id');
    }

    public function trade(): BelongsTo
    {
        return $this->belongsTo(Trade::class);
    }

    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(ConversationReport::class);
    }

    public function getOtherUser($userId)
    {
        if ($this->user1_id === (int) $userId) {
            return $this->user2;
        }
        if ($this->user2_id === (int) $userId) {
            return $this->user1;
        }
        if ($this->customer_id === (int) $userId) {
            return $this->seller?->user;
        }
        if ($this->seller && $this->seller->user_id === (int) $userId) {
            return $this->customer;
        }
        return null;
    }

    public function isParticipant($userId): bool
    {
        $id = (int) $userId;
        if ($this->user1_id && $this->user2_id) {
            return $this->user1_id === $id || $this->user2_id === $id;
        }
        if ($this->customer_id === $id) {
            return true;
        }
        if ($this->seller && $this->seller->user_id === $id) {
            return true;
        }
        return false;
    }

    public function isTradeConversation(): bool
    {
        return $this->trade_id !== null;
    }

    public function isListingConversation(): bool
    {
        return $this->trade_listing_id !== null && $this->trade_id === null;
    }

    public static function firstOrCreateForListing(int $tradeListingId, int $userId1, int $userId2): self
    {
        $user1 = min($userId1, $userId2);
        $user2 = max($userId1, $userId2);

        return self::firstOrCreate(
            [
                'trade_listing_id' => $tradeListingId,
                'user1_id' => $user1,
                'user2_id' => $user2,
            ],
            [
                'subject' => 'Trade listing #' . $tradeListingId,
            ]
        );
    }

    public function unreadCountForUser(int $userId): int
    {
        return $this->messages()
            ->where('sender_id', '!=', $userId)
            ->whereNull('seen_at')
            ->count();
    }
}
