<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionSellerDocument extends Model
{
    protected $fillable = [
        'auction_seller_profile_id',
        'document_type',
        'document_path',
        'status',
        'rejection_reason',
    ];

    public function auctionSellerProfile(): BelongsTo
    {
        return $this->belongsTo(AuctionSellerProfile::class, 'auction_seller_profile_id');
    }
}
