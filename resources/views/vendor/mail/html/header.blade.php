@props(['url', 'logoSrc' => null])
@php
    $logoSrc = $logoSrc ?? (rtrim(config('app.url'), '/') . '/images/logo.png');
@endphp
<tr>
    <td class="header" style="background-color: #ffffff; padding: 32px 44px 24px; text-align: center; border-bottom: 1px solid #e2e8f0;">
        <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">
            <img src="{{ $logoSrc }}" alt="ToyHaven" class="logo" style="max-height: 56px; width: auto; height: auto; display: block; margin: 0 auto;" />
        </a>
        <p style="margin: 12px 0 0; font-size: 12px; letter-spacing: 0.08em; color: #64748b; text-transform: uppercase; font-weight: 500;">Play · Discover · Collect</p>
    </td>
</tr>
