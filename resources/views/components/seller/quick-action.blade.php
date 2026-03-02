@props([
    'href',
    'icon',
    'title',
    'description' => null,
])

<a
    href="{{ $href }}"
    class="quick-action-card card border {{ $attributes->get('class') }}"
    {{ $attributes->except('class') }}
>
    <i class="bi {{ $icon }}"></i>
    <h6>{{ $title }}</h6>
    @if($description)
        <small class="text-muted">{{ $description }}</small>
    @endif
</a>
