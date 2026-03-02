@props([
    'status',
    'label' => null,
])

@php
    $statusColors = [
        'pending' => 'warning',
        'processing' => 'info',
        'packed' => 'primary',
        'shipped' => 'primary',
        'in_transit' => 'info',
        'out_for_delivery' => 'warning',
        'delivered' => 'success',
        'completed' => 'success',
        'cancelled' => 'danger',
        'rejected' => 'danger',
        'approved' => 'success',
        'active' => 'success',
        'inactive' => 'secondary',
    ];
    $color = $statusColors[strtolower($status)] ?? 'secondary';
    $displayLabel = $label ?? ucfirst($status);
@endphp

<span class="badge bg-{{ $color }}" {{ $attributes }}>{{ $displayLabel }}</span>
