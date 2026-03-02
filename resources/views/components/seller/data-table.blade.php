@props([
    'title' => null,
    'subtitle' => null,
])

<div class="card" {{ $attributes->except('class') }}>
    @if($title || isset($actions))
        <div class="card-header d-flex justify-content-between align-items-center">
            <div>
                @if($title)
                    <h5 class="mb-0">{{ $title }}</h5>
                @endif
                @if($subtitle)
                    <div class="text-muted small mt-1">{{ $subtitle }}</div>
                @endif
            </div>
            @if(isset($actions))
                <div>{{ $actions }}</div>
            @endif
        </div>
    @endif
    <div class="card-body p-0">
        {{ $slot }}
    </div>
    @if(isset($footer))
        <div class="card-footer">
            {{ $footer }}
        </div>
    @endif
</div>
