<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'google_id',
        'phone',
        'phone_verified_at',
        'address',
        'city',
        'province',
        'postal_code',
        'is_banned',
        'banned_at',
        'ban_reason',
        'banned_by',
        'related_report_id',
        'last_seen_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'banned_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
        ];
    }

    // Relationships
    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function reviews()
    {
        return $this->hasMany(ProductReview::class);
    }

    public function wishlists()
    {
        return $this->hasMany(Wishlist::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function defaultAddress()
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    public function categoryPreferences()
    {
        return $this->belongsToMany(Category::class, 'user_category_preferences', 'user_id', 'category_id')
                    ->withTimestamps();
    }

    public function bannedBy()
    {
        return $this->belongsTo(User::class, 'banned_by');
    }

    public function relatedReport()
    {
        return $this->belongsTo(Report::class, 'related_report_id');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'reporter_id');
    }

    public function tradeListings()
    {
        return $this->hasMany(TradeListing::class);
    }

    public function tradeOffers()
    {
        return $this->hasMany(TradeOffer::class, 'offerer_id');
    }

    public function tradesAsInitiator()
    {
        return $this->hasMany(Trade::class, 'initiator_id');
    }

    public function tradesAsParticipant()
    {
        return $this->hasMany(Trade::class, 'participant_id');
    }

    public function userProducts()
    {
        return $this->hasMany(UserProduct::class);
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderByDesc('created_at');
    }

    public function activeSubscription()
    {
        return $this->subscriptions()->active()->first();
    }

    public function hasActiveMembership(): bool
    {
        return $this->subscriptions()->active()->exists();
    }

    public function currentPlan(): ?Plan
    {
        $sub = $this->activeSubscription();

        return $sub?->plan;
    }

    public function hasSelectedCategories(): bool
    {
        return $this->categoryPreferences()->count() > 0;
    }

    // Helper methods
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isSeller(): bool
    {
        return $this->role === 'seller' || $this->seller !== null;
    }

    public function isCustomer(): bool
    {
        return $this->role === 'customer';
    }

    public function isPremium(): bool
    {
        return $this->role === 'premium';
    }

    /**
     * Send the email verification notification.
     *
     * @return void
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerifyEmailNotification);
    }

    /**
     * Send the password reset notification.
     *
     * @param  string  $token
     * @return void
     */
    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function isOnline(int $withinSeconds = 30): bool
    {
        return $this->last_seen_at && $this->last_seen_at->diffInSeconds(now(), false) < $withinSeconds;
    }

}
