@props(['user', 'layout' => 'full', 'size' => null])
@php
    $isCompact = $layout === 'compact';
    $size = $size ?? ($isCompact ? '80px' : '160px');
    $fontSize = $isCompact ? '2.5rem' : '4.5rem';

    $avatarHash = md5($user->username . '-avatar');
    $fallbackColor = '#' . substr($avatarHash, 0, 6);
    $avatarUrl = $user->avatar ? asset($user->avatar) : null;
    $id = $user->username;
@endphp
<a href="/users/{{ $id }}">
@if($avatarUrl)
    <img src="{{ $avatarUrl }}" 
         class="rounded-circle shadow"
         style="width: {{ $size }}; height: {{ $size }}; object-fit: cover; position: relative; z-index: 2;"
         alt="{{ $user->username }}'s Avatar"
         {{ $attributes }}>
@else
    <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow border border-4 border-white {{ $isCompact ? 'mx-auto' : '' }}"
         style="width: {{ $size }}; height: {{ $size }}; font-size: {{ $fontSize }}; background-color: {{ $fallbackColor }}; position: relative; z-index: 2;"
         {{ $attributes }}>
        {{ strtoupper(substr($user->username ?? '?', 0, 1)) }}
    </div>
@endif
</a>