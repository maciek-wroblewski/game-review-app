@props(['user', 'size' => '160px'])
@php
    $sizeValue = (int) str_replace(['px', 'rem', 'em', 'vw', 'vh', '%'], '', $size);
    $fontSize = max(12, round($sizeValue * 0.45)) . 'px';
    $iconSize = max(12, round($sizeValue * 0.3)) . 'px';

    $avatarHash = md5($user->username . '-avatar');
    $fallbackColor = '#' . substr($avatarHash, 0, 6);
    $avatarUrl = $user->avatar ? asset($user->avatar) : null;
    $id = $user->username;
@endphp
<a href="/users/{{ $id }}" style="text-decoration: none;">
    @if($user->is_suspended)
    <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow border border-4 border-white mx-auto"
        style="width: {{ $size }}; height: {{ $size }}; font-size: {{ $fontSize }}; background-color: black; z-index: 2;"
        {{ $attributes }}>
        <i class="bi bi-slash-circle-fill text-danger" style="font-size: {{ $iconSize }}; filter: drop-shadow(0 0 15px rgba(220,53,69,0.7));"></i>
    </div>
    @elseif($avatarUrl)
    <img src="{{ $avatarUrl }}" class="rounded-circle shadow"
        style="width: {{ $size }}; height: {{ $size }}; object-fit: cover; position: relative;"
        alt="{{ $user->username }}'s Avatar" {{ $attributes }}>
    @else
    <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow border border-4 border-white mx-auto"
        style="width: {{ $size }}; height: {{ $size }}; font-size: {{ $fontSize }}; background-color: {{ $fallbackColor }}; position: relative; z-index: 2;"
        {{ $attributes }}>
        {{ strtoupper(substr($user->username ?? '?', 0, 1)) }}
    </div>
    @endif
</a>