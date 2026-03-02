@props([
    'icon' => 'bi-inbox',
    'message' => 'No data to display',
    'action' => null,
    'actionLabel' => null,
])

<div class="text-center py-5">
    <i class="bi {{ $icon }} text-muted" style="font-size: 3rem;"></i>
    <p class="text-muted mt-3 mb-0">{{ $message }}</p>
    @if($action && $actionLabel)
        <a href="{{ $action }}" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle me-1"></i>{{ $actionLabel }}
        </a>
    @endif
    @if(isset($slot) && trim($slot))
        <div class="mt-3">{{ $slot }}</div>
    @endif
</div>
