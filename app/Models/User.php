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
        'moderator_permissions',
        'trade_suspended',
        'trade_suspended_at',
        'trade_suspension_reason',
        'trade_suspended_by',
        'trade_suspension_offence_count',
        'trade_suspended_until',
        'auction_suspended_until',
        'auction_banned_at',
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
            'trade_suspended' => 'boolean',
            'trade_suspended_at' => 'datetime',
            'trade_suspended_until' => 'datetime',
            'auction_suspended_until' => 'datetime',
            'auction_banned_at' => 'datetime',
        ];
    }

    // Relationships
    public function seller()
    {
        return $this->hasOne(Seller::class);
    }

    public function sellerModeratorAssignments()
    {
        return $this->hasMany(SellerModerator::class);
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

    public function savedTradeListings()
    {
        return $this->hasMany(SavedTradeListing::class);
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

    public function moderatorActions()
    {
        return $this->hasMany(ModeratorAction::class, 'moderator_id');
    }

    public function assignedDisputes()
    {
        return $this->hasMany(OrderDispute::class, 'assigned_to');
    }

    public function subscriptions()
    {
        return $this->hasMany(Subscription::class)->orderByDesc('created_at');
    }

    public function auctionSellerVerifications()
    {
        return $this->hasMany(AuctionSellerVerification::class);
    }

    public function hasPlan(string $slug): bool
    {
        $plan = $this->currentPlan();
        return $plan && $plan->slug === $slug;
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

    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }

    public function canModerate(): bool
    {
        return $this->isModerator() || $this->isAdmin();
    }

    public function isTradeSuspended(): bool
    {
        if (! $this->trade_suspended) {
            return false;
        }
        if ($this->trade_suspended_until === null) {
            return true; // permanent ban
        }
        if ($this->trade_suspended_until->isPast()) {
            $this->update([
                'trade_suspended' => false,
                'trade_suspended_at' => null,
                'trade_suspended_until' => null,
                'trade_suspension_reason' => null,
                'trade_suspended_by' => null,
            ]);
            return false;
        }
        return true;
    }

    public function tradeSuspendedBy(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'trade_suspended_by');
    }

    /**
     * Get the seller this user can access (own seller or moderated seller).
     */
    public function getSellerForDashboard(): ?Seller
    {
        if ($this->seller) {
            return $this->seller;
        }
        $assignment = $this->sellerModeratorAssignments()->with('seller')->first();
        return $assignment?->seller;
    }

    /**
     * Get the moderator assignment for current seller (if user is moderator, not owner).
     */
    public function getModeratorAssignment(): ?SellerModerator
    {
        return $this->sellerModeratorAssignments()->first();
    }

    /**
     * Check if user can access seller dashboard (owner or moderator).
     */
    public function canAccessSellerDashboard(): bool
    {
        return $this->seller !== null || $this->sellerModeratorAssignments()->exists();
    }

    /**
     * Check if user has a specific permission for the seller they're accessing.
     */
    public function hasSellerPermission(string $perm): bool
    {
        if ($this->seller) {
            return true; // Owner has all permissions
        }
        $assignment = $this->getModeratorAssignment();
        return $assignment && $assignment->hasPermission($perm);
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
