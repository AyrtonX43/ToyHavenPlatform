<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Notifications\Notifiable;

class Seller extends Model
{
    use Notifiable;
    protected $fillable = [
        'user_id',
        'business_name',
        'business_slug',
        'description',
        'logo',
        'phone',
        'email',
        'address',
        'city',
        'province',
        'postal_code',
        'toy_category_ids',
        'verification_status',
        'rejection_reason',
        'rating',
        'total_reviews',
        'total_sales',
        'response_rate',
        'average_response_time',
        'is_active',
        'is_verified_shop',
        'suspension_reason',
        'suspended_at',
        'suspended_by',
        'related_report_id',
    ];

    protected $casts = [
        'toy_category_ids' => 'array',
        'rating' => 'decimal:2',
        'response_rate' => 'decimal:2',
        'is_active' => 'boolean',
        'is_verified_shop' => 'boolean',
        'suspended_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(SellerReview::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(SellerDocument::class);
    }

    public function pageSettings(): HasOne
    {
        return $this->hasOne(BusinessPageSetting::class);
    }

    public function socialLinks(): HasMany
    {
        return $this->hasMany(BusinessSocialLink::class)->orderBy('display_order');
    }

    public function activeSocialLinks(): HasMany
    {
        return $this->hasMany(BusinessSocialLink::class)->where('is_active', true)->orderBy('display_order');
    }

    public function businessPageRevisions(): HasMany
    {
        return $this->hasMany(BusinessPageRevision::class)->orderBy('created_at', 'desc');
    }

    public function pendingBusinessPageRevisions(): HasMany
    {
        return $this->hasMany(BusinessPageRevision::class)->where('status', \App\Models\BusinessPageRevision::STATUS_PENDING);
    }

    public function hasVerifiedBusinessEmail(): bool
    {
        return !empty($this->email_verified_at);
    }

    public function hasVerifiedBusinessPhone(): bool
    {
        return !empty($this->phone_verified_at);
    }

    public function businessHours(): HasMany
    {
        return $this->hasMany(BusinessHour::class)->orderByRaw("FIELD(day_of_week, 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday')");
    }

    // Helper methods
    public function isVerified(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function getRankingBadge(): string
    {
        if ($this->rating >= 4.5 && $this->total_reviews >= 50) {
            return 'Top Seller';
        } elseif ($this->rating >= 4.0) {
            return 'Verified';
        }
        return 'New';
    }

    public function suspendedBy()
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function relatedReport()
    {
        return $this->belongsTo(Report::class, 'related_report_id');
    }

    public function tradeListings()
    {
        return $this->hasMany(TradeListing::class);
    }

    public function tradeOffers()
    {
        return $this->hasMany(TradeOffer::class, 'offerer_seller_id');
    }

    public function tradesAsInitiator()
    {
        return $this->hasMany(Trade::class, 'initiator_seller_id');
    }

    public function tradesAsParticipant()
    {
        return $this->hasMany(Trade::class, 'participant_seller_id');
    }
}
