@props([
    'title',
    'subtitle' => null,
])

<div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
    <div>
        <h4 class="mb-1">{{ $title }}</h4>
        @if($subtitle)
            <p class="text-muted mb-0">{{ $subtitle }}</p>
        @endif
    </div>
    @if(isset($actions))
        <div class="d-flex gap-2 flex-wrap">{{ $actions }}</div>
    @endif
</div>
