<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class BusinessPageRevision extends Model
{
    protected $fillable = [
        'seller_id',
        'type',
        'payload',
        'status',
        'rejection_reason',
        'reviewed_at',
        'reviewed_by',
    ];

    protected $casts = [
        'payload' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public const TYPE_GENERAL = 'general';
    public const TYPE_CONTACT = 'contact';
    public const TYPE_SOCIAL = 'social';
    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function reviewedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    /**
     * Apply this revision to live data (page settings, seller, social links).
     */
    public function apply(): void
    {
        if ($this->status !== self::STATUS_PENDING) {
            return;
        }

        $payload = $this->payload ?? [];

        switch ($this->type) {
            case self::TYPE_GENERAL:
                $this->applyGeneral($payload);
                break;
            case self::TYPE_CONTACT:
                $this->applyContact($payload);
                break;
            case self::TYPE_SOCIAL:
                $this->applySocial($payload);
                break;
        }

        $this->update([
            'status' => self::STATUS_APPROVED,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);
    }

    protected function applyGeneral(array $payload): void
    {
        $pageSettings = $this->seller->pageSettings ?? BusinessPageSetting::create(['seller_id' => $this->seller->id]);

        $revisionDir = "business/revisions/{$this->id}";
        $logoPath = $payload['logo_path'] ?? null;
        $bannerPath = $payload['banner_path'] ?? null;

        if ($logoPath && Storage::disk('public')->exists($logoPath)) {
            $newPath = 'business/logos/' . basename($logoPath);
            Storage::disk('public')->copy($logoPath, $newPath);
            if ($pageSettings->logo_path && Storage::disk('public')->exists($pageSettings->logo_path)) {
                Storage::disk('public')->delete($pageSettings->logo_path);
            }
            $payload['logo_path'] = $newPath;
        }
        if ($bannerPath && Storage::disk('public')->exists($bannerPath)) {
            $newPath = 'business/banners/' . basename($bannerPath);
            Storage::disk('public')->copy($bannerPath, $newPath);
            if ($pageSettings->banner_path && Storage::disk('public')->exists($pageSettings->banner_path)) {
                Storage::disk('public')->delete($pageSettings->banner_path);
            }
            $payload['banner_path'] = $newPath;
        }

        $allowed = ['page_name', 'business_description', 'logo_path', 'banner_path', 'primary_color', 'secondary_color', 'layout_type', 'meta_title', 'meta_description', 'keywords', 'is_published', 'published_at'];
        $data = array_intersect_key(array_filter($payload, fn($v) => $v !== null), array_flip($allowed));
        if (isset($data['is_published']) && !empty($data['is_published'])) {
            $data['published_at'] = $pageSettings->published_at ?? now();
        } elseif (isset($data['is_published'])) {
            $data['published_at'] = null;
        }
        $pageSettings->update($data);

        if (Storage::disk('public')->exists($revisionDir)) {
            Storage::disk('public')->deleteDirectory($revisionDir);
        }
    }

    protected function applyContact(array $payload): void
    {
        $this->seller->update([
            'email' => $payload['email'] ?? $this->seller->email,
            'phone' => $payload['phone'] ?? $this->seller->phone,
            'email_verified_at' => isset($payload['email']) ? null : $this->seller->email_verified_at,
            'phone_verified_at' => isset($payload['phone']) ? null : $this->seller->phone_verified_at,
        ]);
    }

    protected function applySocial(array $payload): void
    {
        BusinessSocialLink::where('seller_id', $this->seller->id)->delete();

        $links = $payload['social_links'] ?? [];
        foreach ($links as $index => $link) {
            if (!empty($link['url'])) {
                BusinessSocialLink::create([
                    'seller_id' => $this->seller->id,
                    'platform' => $link['platform'],
                    'url' => $link['url'],
                    'display_name' => $link['display_name'] ?? null,
                    'display_order' => $index,
                    'is_active' => $link['is_active'] ?? true,
                ]);
            }
        }
    }

    public function reject(string $reason = null): void
    {
        $this->update([
            'status' => self::STATUS_REJECTED,
            'rejection_reason' => $reason,
            'reviewed_at' => now(),
            'reviewed_by' => auth()->id(),
        ]);

        if ($this->type === self::TYPE_GENERAL) {
            $revisionDir = "business/revisions/{$this->id}";
            if (Storage::disk('public')->exists($revisionDir)) {
                Storage::disk('public')->deleteDirectory($revisionDir);
            }
        }
    }
}
