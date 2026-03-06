<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuctionBid extends Model
{
    protected $fillable = [
        'auction_id',
        'user_id',
        'amount',
        'anonymous_display_id',
        'is_winning',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_winning' => 'boolean',
    ];

    public function auction(): BelongsTo
    {
        return $this->belongsTo(Auction::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function generateAnonymousDisplayId(): string
    {
        $chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $id = 'Bidder #';
        for ($i = 0; $i < 6; $i++) {
            $id .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $id;
    }
}
