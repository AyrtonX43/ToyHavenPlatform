<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SellerModerator extends Model
{
    protected $fillable = ['seller_id', 'user_id', 'permissions'];

    protected $casts = [
        'permissions' => 'array',
    ];

    public const PERM_PRODUCTS = 'products';
    public const PERM_ORDERS = 'orders';
    public const PERM_BUSINESS_PAGE = 'business_page';

    public static function validPermissions(): array
    {
        return [self::PERM_PRODUCTS, self::PERM_ORDERS, self::PERM_BUSINESS_PAGE];
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasPermission(string $perm): bool
    {
        $perms = $this->permissions ?? [];
        return in_array($perm, $perms) || in_array('*', $perms);
    }
}
