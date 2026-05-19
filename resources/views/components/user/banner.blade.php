@props(['user', 'layout' => 'full', 'height' => null])

@php
    if ($height === null) {
        $isCompact = $layout === 'compact';
        $height = $isCompact ? '100px' : '220px';
    }
    
    $bannerHash = md5($user->username . '-banner');
    $color1 = '#' . substr($bannerHash, 0, 6);
    $color2 = '#' . substr($bannerHash, 6, 6);
@endphp

@if($user->banner)
    <img src="{{ asset($user->banner) }}" class="w-100" style="height: {{ $height }}; object-fit: cover;" alt="{{ $user->username }}'s Banner">
@else
    <div style="height: {{ $height }}; background: linear-gradient(135deg, {{ $color1 }} 0%, {{ $color2 }} 100%);"></div>
@endif
