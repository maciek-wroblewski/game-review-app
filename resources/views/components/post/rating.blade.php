@props(['rating'])
@php
    $meterColor = match(true) {
        $rating >= 7 => 'bg-success',
        $rating >= 4 => 'bg-warning',
        default => 'bg-danger',
    };
@endphp

<div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end" style="width: 50px; min-width: 50px; background-color: rgba(0, 0, 0, 0.05); z-index: 1;">
    <div class="js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition {{ $meterColor }}" style="height: {{ $rating * 10 }}%; transition: height 0.3s, background-color 0.3s;">
        <span class="js-meter-text" style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 0.85rem;">{{ $rating }} / 10</span>
    </div>
    <div class="js-rating-overlay position-absolute top-0 start-0 w-100 h-100 d-none cursor-crosshair" style="z-index: 10;" title="Click to set new rating"></div>
</div>