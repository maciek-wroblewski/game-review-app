@props(['link' => null, 'href' => null])

@php
    $url = $link ?? $href ?? '#';
@endphp

<div {{ $attributes->merge(['class' => 'nested-card']) }} data-card-link="{{ $url }}">
    {{ $slot }}
</div>
