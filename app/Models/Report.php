<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Report extends Model
{
    public const AUCTION_REPORT_TYPES = [
        'counterfeit_item' => 'Counterfeit / Fake Item',
        'item_not_as_described' => 'Item Not as Described',
        'seller_not_shipping' => 'Seller Not Shipping',
        'damaged_in_transit' => 'Damaged in Transit',
        'auction_manipulation' => 'Bid Manipulation / Shill Bidding',
    ];

    protected $fillable = [
        'reporter_id',
        'reportable_type',
        'reportable_id',
        'auction_id',
        'auction_payment_id',
        'report_type',
        'reason',
        'description',
        'evidence',
        'status',
        'admin_notes',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'evidence' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function auctionPayment(): BelongsTo
    {
        return $this->belongsTo(AuctionPayment::class);
    }
}
