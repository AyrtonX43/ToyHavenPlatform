@props([
    'icon',
    'label',
    'value',
    'variant' => 'primary',
    'animate' => false,
    'currency' => false,
])

@php
    $rawValue = is_numeric($value) ? (float) $value : (float) preg_replace('/[^0-9.]/', '', (string) $value);
@endphp

<div
    class="seller-stat-card seller-stat-card--{{ $variant }} stat-card bg-{{ $variant }}"
    {{ $attributes->merge(['class' => '']) }}
>
    <i class="bi {{ $icon }} seller-stat-card__icon stat-icon"></i>
    <div class="seller-stat-card__label stat-label">{{ $label }}</div>
    <div class="seller-stat-card__value stat-value">
        @if($animate)
            @if($currency)
                <span class="counter-currency" data-count="{{ $rawValue }}">₱0.00</span>
            @else
                <span class="counter-number" data-count="{{ $rawValue }}">0</span>
            @endif
        @else
            {{ $value }}
        @endif
    </div>
</div>
