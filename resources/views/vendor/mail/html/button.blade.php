@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
@php
    $buttonStyle = match($color) {
        'success', 'green' => 'background-color: #0d9488; color: #ffffff !important; border: 1px solid #0d9488;',
        'error', 'red' => 'background-color: #b91c1c; color: #ffffff !important; border: 1px solid #b91c1c;',
        default => 'background-color: #1e3a5f; color: #ffffff !important; border: 1px solid #1e3a5f;',
    };
@endphp
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation">
<tr>
<td>
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener" style="display: inline-block; padding: 14px 28px; font-size: 15px; font-weight: 600; text-decoration: none; border-radius: 6px; {{ $buttonStyle }}">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
