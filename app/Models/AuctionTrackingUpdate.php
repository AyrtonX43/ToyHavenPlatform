<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionTrackingUpdate extends Model
{
    protected $fillable = [
        'auction_payment_id',
        'tracking_number',
        'carrier',
        'notes',
        'updated_by',
    ];

    public function auctionPayment(): BelongsTo
    {
        return $this->belongsTo(AuctionPayment::class, 'auction_payment_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
