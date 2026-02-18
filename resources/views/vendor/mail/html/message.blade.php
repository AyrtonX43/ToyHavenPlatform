@php
    $logoPath = public_path('images/logo.png');
    $logoSrc = null;
    if (isset($message) && method_exists($message, 'embed') && file_exists($logoPath)) {
        $logoSrc = $message->embed($logoPath);
    }
    if (!$logoSrc) {
        $logoSrc = rtrim(config('app.url'), '/') . '/images/logo.png';
    }
@endphp
<x-mail::layout>
{{-- Header with embedded logo --}}
<x-slot:header>
<x-mail::header :url="config('app.url')" :logoSrc="$logoSrc" />
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
© {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
