<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AuctionSellerVerification extends Model
{
    protected $fillable = [
        'user_id',
        'seller_id',
        'seller_type',
        'auction_business_name',
        'status',
        'rejection_reason',
        'phone',
        'address',
        'selfie_path',
        'verified_at',
        'verified_by',
        'is_suspended',
        'suspended_at',
        'suspension_reason',
        'suspended_by',
    ];

    protected $casts = [
        'verified_at' => 'datetime',
        'suspended_at' => 'datetime',
        'is_suspended' => 'boolean',
    ];

    public const INDIVIDUAL_DOCUMENTS = [
        'government_id_1', 'government_id_2', 'government_id_3',
        'bank_statement',
    ];

    public const BUSINESS_DOCUMENTS = [
        'government_id_1',
        'bank_statement',
        'business_permit',
        'dti_registration',
        'sec_registration',
        'bir_certificate',
        'official_receipt_sample',
    ];

    public const INDIVIDUAL_REQUIRED = [
        'government_id_1', 'government_id_2', 'bank_statement',
    ];

    public const BUSINESS_REQUIRED = [
        'government_id_1', 'bank_statement', 'business_permit',
        'bir_certificate', 'official_receipt_sample',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function verifiedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function suspendedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'suspended_by');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(AuctionSellerDocument::class, 'verification_id');
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSynced(): bool
    {
        return $this->seller_id !== null;
    }

    public function getDisplayName(): string
    {
        if ($this->auction_business_name) {
            return $this->auction_business_name;
        }

        if ($this->seller && $this->seller->business_name) {
            return $this->seller->business_name;
        }

        return $this->user->name ?? 'Unknown';
    }

    public function getRequiredDocuments(): array
    {
        return $this->seller_type === 'business'
            ? self::BUSINESS_REQUIRED
            : self::INDIVIDUAL_REQUIRED;
    }

    public function getAllowedDocuments(): array
    {
        return $this->seller_type === 'business'
            ? self::BUSINESS_DOCUMENTS
            : self::INDIVIDUAL_DOCUMENTS;
    }

    public static function documentLabel(string $type): string
    {
        return match ($type) {
            'government_id_1' => 'Government ID #1',
            'government_id_2' => 'Government ID #2',
            'government_id_3' => 'Government ID #3 (Optional)',
            'bank_statement' => 'Bank Statement',
            'business_permit' => 'Business Permit',
            'dti_registration' => 'DTI Registration',
            'sec_registration' => 'SEC Registration',
            'bir_certificate' => 'BIR Certificate of Registration',
            'official_receipt_sample' => 'Official Receipt Sample',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }
}
