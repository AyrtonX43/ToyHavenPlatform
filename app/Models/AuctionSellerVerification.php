<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionSellerVerification extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'type',
        'verification_status',
        'rejection_reason',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AuctionSellerDocument::class, 'verification_id');
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function isPending(): bool
    {
        return in_array($this->verification_status, ['pending', 'requires_resubmission']);
    }
}
