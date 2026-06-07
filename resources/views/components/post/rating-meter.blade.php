@props(['rating' => 0, 'editable' => false, 'class' => '', 'name' => 'rating'])

@php
    $meterColor = match(true) { 
        $rating >= 7 => 'bg-success', 
        $rating >= 4 => 'bg-warning', 
        default => 'bg-danger' 
    };
@endphp



<div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end {{ $class }}" 
     style="width: 50px; min-width: 50px; background-color: rgba(0,0,0,0.05); z-index: 1;"
     data-original-rating="{{ $rating }}"
     data-current-rating="{{ $rating }}">
    
    <div class="js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition {{ $meterColor }}" 
         style="height: {{ $rating * 10 }}%; transition: height 0.3s, background-color 0.3s;">
        <span class="js-meter-text" style="font-size: 0.85rem;">{{ $rating }} / 10</span>
    </div>

    <div class="js-meter-overlay position-absolute top-0 start-0 w-100 h-100" 
         style="z-index: 10; {{ $editable ? '' : 'display: none;' }}" 
         title="Click and drag to set rating"></div>
</div>
