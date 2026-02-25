<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderTracking extends Model
{
    protected $table = 'order_tracking';

    protected $fillable = [
        'order_id',
        'status',
        'description',
        'tracking_number',
        'updated_by',
        'location',
        'estimated_delivery_date',
        'notes',
    ];

    protected $casts = [
        'estimated_delivery_date' => 'date',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function getStatusLabel(): string
    {
        return match($this->status) {
            'order_placed' => 'Order Placed',
            'payment_confirmed' => 'Payment Confirmed',
            'processing' => 'Processing',
            'packed' => 'Packed',
            'shipped' => 'Shipped',
            'in_transit' => 'In Transit',
            'out_for_delivery' => 'Out for Delivery',
            'delivered' => 'Delivered',
            default => 'Unknown',
        };
    }
}
