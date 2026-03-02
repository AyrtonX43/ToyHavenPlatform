@props([
    'type' => 'info',
    'icon' => null,
    'heading' => null,
    'dismissible' => true,
])

@php
    $icons = [
        'success' => 'bi-check-circle',
        'danger' => 'bi-x-circle',
        'warning' => 'bi-hourglass-split',
        'info' => 'bi-info-circle',
    ];
    $iconClass = $icon ?? ($icons[$type] ?? 'bi-info-circle');
@endphp

<div class="alert alert-{{ $type }} {{ $dismissible ? 'alert-dismissible fade show' : '' }}" role="alert" {{ $attributes->except('class') }}>
    @if($heading)
        <h5 class="alert-heading">
            <i class="bi {{ $iconClass }} me-2"></i>{{ $heading }}
        </h5>
    @else
        <i class="bi {{ $iconClass }} me-2"></i>
    @endif
    {{ $slot }}
    @if($dismissible)
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    @endif
</div>
