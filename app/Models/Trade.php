<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Trade extends Model
{
    protected $fillable = [
        'trade_listing_id',
        'trade_offer_id',
        'initiator_id',
        'initiator_seller_id',
        'participant_id',
        'participant_seller_id',
        'cash_amount',
        'status',
        'meetup_location_lat',
        'meetup_location_lng',
        'meetup_location_address',
        'meetup_scheduled_at',
        'meetup_completed_at',
        'completed_at',
        'initiator_locked_at',
        'participant_locked_at',
        'initiator_confirmed_meetup_at',
        'participant_confirmed_meetup_at',
        'initiator_cancel_requested_at',
        'participant_cancel_requested_at',
    ];

    protected $casts = [
        'cash_amount' => 'decimal:2',
        'meetup_location_lat' => 'decimal:7',
        'meetup_location_lng' => 'decimal:7',
        'meetup_scheduled_at' => 'datetime',
        'meetup_completed_at' => 'datetime',
        'completed_at' => 'datetime',
        'initiator_locked_at' => 'datetime',
        'participant_locked_at' => 'datetime',
        'initiator_confirmed_meetup_at' => 'datetime',
        'participant_confirmed_meetup_at' => 'datetime',
        'initiator_cancel_requested_at' => 'datetime',
        'participant_cancel_requested_at' => 'datetime',
    ];

    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }

    public function tradeOffer(): BelongsTo
    {
        return $this->belongsTo(TradeOffer::class);
    }

    public function initiator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'initiator_id');
    }

    public function initiatorSeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'initiator_seller_id');
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(User::class, 'participant_id');
    }

    public function participantSeller(): BelongsTo
    {
        return $this->belongsTo(Seller::class, 'participant_seller_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(TradeItem::class);
    }

    public function initiatorItems(): HasMany
    {
        return $this->hasMany(TradeItem::class)->where('side', 'initiator');
    }

    public function participantItems(): HasMany
    {
        return $this->hasMany(TradeItem::class)->where('side', 'participant');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }

    public function dispute(): HasOne
    {
        return $this->hasOne(TradeDispute::class);
    }

    public function proofs(): HasMany
    {
        return $this->hasMany(TradeProof::class);
    }

    public function bothSubmittedProof(): bool
    {
        $initiatorSubmitted = $this->proofs()->where('user_id', $this->initiator_id)->exists();
        $participantSubmitted = $this->proofs()->where('user_id', $this->participant_id)->exists();
        return $initiatorSubmitted && $participantSubmitted;
    }

    public function requestCancel(int $userId): void
    {
        if ($this->initiator_id === $userId) {
            $this->update(['initiator_cancel_requested_at' => now()]);
        } elseif ($this->participant_id === $userId) {
            $this->update(['participant_cancel_requested_at' => now()]);
        }
    }

    public function bothRequestedCancel(): bool
    {
        return $this->initiator_cancel_requested_at && $this->participant_cancel_requested_at;
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(TradeReview::class);
    }

    public function getStatusLabel(): string
    {
        return match ($this->status) {
            'pending_meetup' => 'Pending Meetup',
            'meetup_scheduled' => 'Meetup Scheduled',
            'meetup_completed' => 'Meetup Completed',
            'completed' => 'Completed',
            'disputed' => 'Disputed',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function getOtherParty($userId)
    {
        if ($this->initiator_id === $userId) {
            return $this->participant;
        }
        return $this->initiator;
    }

    public function isInitiator($userId): bool
    {
        return $this->initiator_id === $userId;
    }

    public function isParticipant($userId): bool
    {
        return $this->participant_id === $userId;
    }

    public function bothLocked(): bool
    {
        return $this->initiator_locked_at && $this->participant_locked_at;
    }

    public function bothConfirmedMeetup(): bool
    {
        return $this->initiator_confirmed_meetup_at && $this->participant_confirmed_meetup_at;
    }
}
