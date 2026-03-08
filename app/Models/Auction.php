<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Auction extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'seller_type',
        'product_id',
        'user_product_id',
        'category_id',
        'title',
        'description',
        'starting_bid',
        'bid_increment',
        'start_at',
        'end_at',
        'status',
        'winner_id',
        'winning_amount',
        'bids_count',
        'terms_accepted_at',
    ];

    protected function casts(): array
    {
        return [
            'starting_bid' => 'decimal:2',
            'bid_increment' => 'decimal:2',
            'winning_amount' => 'decimal:2',
            'start_at' => 'datetime',
            'end_at' => 'datetime',
            'terms_accepted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePendingApproval($query)
    {
        return $query->where('status', 'pending_approval');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
