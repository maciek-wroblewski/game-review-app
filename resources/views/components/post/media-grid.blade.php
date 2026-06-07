@props(['media' => []])

@php
    $mediaCollection = collect($media);
    $count = $mediaCollection->count();
    
    $gridClass = match($count) {
        0 => '',
        1 => 'grid-1',
        2 => 'grid-2',
        3 => 'grid-3',
        default => 'grid-4'
    };

    $mediaData = $mediaCollection->map(function($m) {
        return [
            'url' => asset($m->file_path),
            'type' => str_starts_with($m->mime_type, 'video/') ? 'video' : 'image'
        ];
    })->toJson();
@endphp

@if($count > 0)
<div class="js-media-container" data-media="{{ $mediaData }}">
    
    {{-- Dynamically set the aspect ratio via a CSS variable: 1/1 for a single item, 16/9 for galleries --}}
    <div class="media-grid {{ $gridClass }} shadow-sm w-100" style="--grid-aspect-ratio: {{ $count === 1 ? '1/1' : '16/9' }};">
        @foreach($mediaCollection->take(4) as $index => $item)
        <div class="media-item-wrapper js-lightbox-trigger" data-index="{{ $index }}">
            
            @if(str_starts_with($item->mime_type, 'video/'))
                <video src="{{ $item->file_path }}" class="media-element" muted></video>
                <div class="video-play-overlay">
                    <i class="bi bi-play-fill fs-4"></i>
                </div>
            @else
                <img src="{{ $item->file_path }}" class="media-element" alt="Media item">
            @endif

            @if($count > 4 && $index === 3)
                <div class="media-more-overlay">
                    <span class="text-white fs-2 fw-bold">+{{ $count - 4 }}</span>
                </div>
            @endif

        </div>
        @endforeach
    </div>
</div>
@endif
