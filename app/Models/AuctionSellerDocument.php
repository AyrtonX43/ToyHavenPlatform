<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionSellerDocument extends Model
{
    protected $table = 'auction_seller_documents';

    protected $fillable = [
        'verification_id',
        'document_type',
        'document_path',
    ];

    public function verification(): BelongsTo
    {
        return $this->belongsTo(AuctionSellerVerification::class, 'verification_id');
    }
}
