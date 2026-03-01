<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_number',
        'receipt_number',
        'receipt_path',
        'receipt_generated_at',
        'user_id',
        'seller_id',
        'total_amount',
        'admin_commission',
        'admin_commission_rate',
        'tax_amount',
        'tax_rate',
        'transaction_fee',
        'seller_earnings',
        'shipping_fee',
        'status',
        'payment_status',
        'payment_method',
        'payment_reference',
        'shipping_address',
        'shipping_phone',
        'shipping_city',
        'shipping_province',
        'shipping_postal_code',
        'shipping_notes',
        'tracking_number',
        'estimated_delivery_date',
        'delivered_at',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'admin_commission' => 'decimal:2',
        'admin_commission_rate' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'tax_rate' => 'decimal:2',
        'transaction_fee' => 'decimal:2',
        'seller_earnings' => 'decimal:2',
        'shipping_fee' => 'decimal:2',
        'estimated_delivery_date' => 'date',
        'delivered_at' => 'datetime',
        'receipt_generated_at' => 'datetime',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function tracking(): HasMany
    {
        return $this->hasMany(OrderTracking::class)->orderBy('created_at');
    }

    public function latestTracking(): BelongsTo
    {
        return $this->belongsTo(OrderTracking::class)->latestOfMany();
    }

    public function deliveryConfirmation(): HasOne
    {
        return $this->hasOne(DeliveryConfirmation::class);
    }

    public function disputes(): HasMany
    {
        return $this->hasMany(OrderDispute::class);
    }

    public function activeDispute(): HasOne
    {
        return $this->hasOne(OrderDispute::class)->whereIn('status', ['open', 'investigating'])->latestOfMany();
    }

    // Helper methods
    public function getTotalAttribute(): float
    {
        // Total includes base amount, commission, tax, transaction fee, and shipping
        $totalAmount = (float) ($this->attributes['total_amount'] ?? 0);
        $adminCommission = (float) ($this->attributes['admin_commission'] ?? 0);
        $taxAmount = (float) ($this->attributes['tax_amount'] ?? 0);
        $transactionFee = (float) ($this->attributes['transaction_fee'] ?? 0);
        $shippingFee = (float) ($this->attributes['shipping_fee'] ?? 0);
        
        return $totalAmount + $adminCommission + $taxAmount + $transactionFee + $shippingFee;
    }

    public function getFinalPriceAttribute(): float
    {
        // Final price customer pays (base + commission + tax + fee + shipping)
        return $this->getTotalAttribute();
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'processing']);
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'pending' => 'Order Placed',
            'processing' => 'Processing',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }

    public function hasReceipt(): bool
    {
        return !empty($this->receipt_path);
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered';
    }

    public function isDeliveryConfirmed(): bool
    {
        return $this->deliveryConfirmation()->exists();
    }

    public function hasActiveDispute(): bool
    {
        return $this->activeDispute()->exists();
    }

    public function canBeReviewed(): bool
    {
        return $this->isDeliveryConfirmed() && $this->payment_status === 'paid';
    }

    public function needsDeliveryConfirmation(): bool
    {
        return $this->isDelivered() && !$this->isDeliveryConfirmed() && !$this->hasActiveDispute();
    }
}
