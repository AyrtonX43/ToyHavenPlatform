<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'order_number',
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
        'courier_name',
        'estimated_delivery_date',
        'delivered_at',
        'receipt_confirmed_at',
        'has_dispute',
        'cancelled_at',
        'cancellation_reason',
        'cancelled_by',
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
        'receipt_confirmed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'has_dispute' => 'boolean',
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

    public function receipt()
    {
        return $this->hasOne(OrderReceipt::class);
    }

    public function dispute()
    {
        return $this->hasOne(OrderDispute::class);
    }

    public function cancelledByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    // Helper methods
    public function getTotalAttribute(): float
    {
        // Total includes base amount, commission, tax, transaction fee, and shipping
        return ($this->total_amount + $this->admin_commission + $this->tax_amount + $this->transaction_fee + $this->shipping_fee);
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

    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'packed' => 'primary',
            'shipped' => 'primary',
            'in_transit' => 'primary',
            'out_for_delivery' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function getPaymentStatusColorAttribute(): string
    {
        return match($this->payment_status) {
            'pending' => 'warning',
            'paid' => 'success',
            'failed' => 'danger',
            'refunded' => 'info',
            default => 'secondary',
        };
    }

    public function isDelivered(): bool
    {
        return $this->status === 'delivered' && $this->delivered_at !== null;
    }

    public function hasReceipt(): bool
    {
        return $this->receipt_confirmed_at !== null;
    }

    public function needsReceiptConfirmation(): bool
    {
        return $this->isDelivered() && !$this->hasReceipt() && !$this->has_dispute;
    }

    public function canOpenDispute(): bool
    {
        return $this->isDelivered() && !$this->has_dispute && !$this->hasReceipt();
    }

    public function canReview(): bool
    {
        return $this->hasReceipt() && $this->payment_status === 'paid';
    }

    public function isPending(): bool
    {
        return $this->payment_status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function restoreStock(): void
    {
        foreach ($this->items as $item) {
            if ($item->product) {
                $item->product->increment('stock_quantity', $item->quantity);
            }
        }
    }
}
