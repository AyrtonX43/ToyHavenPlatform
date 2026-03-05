<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedTradeListing extends Model
{
    protected $fillable = ['user_id', 'trade_listing_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tradeListing(): BelongsTo
    {
        return $this->belongsTo(TradeListing::class);
    }
}
