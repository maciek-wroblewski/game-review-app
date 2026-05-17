@props(['media' => []])

@php
    // Ensure we are working with a collection
    $mediaCollection = collect($media);
    $count = $mediaCollection->count();
    
    // Determine the layout grid class
    $gridClass = match($count) {
        0 => '',
        1 => 'grid-1',
        2 => 'grid-2',
        3 => 'grid-3',
        default => 'grid-4'
    };

    // Fixes the JS bug: safe payload for your lightbox script
    $lightboxPayload = $mediaCollection->map(fn($item) => [
        'file_path' => $item->file_path,
        'mime_type' => $item->mime_type
    ])->toJson();
@endphp

@if($count > 0)
<div class="js-media-container mb-3" data-full-media="{{ $lightboxPayload }}">
    {{-- Dynamically set the aspect ratio via a CSS variable: 1/1 for a single item, 16/9 for galleries --}}
    <div class="media-grid {{ $gridClass }} shadow-sm w-100" style="--grid-aspect-ratio: {{ $count === 1 ? '1/1' : '16/9' }};">
        @foreach($mediaCollection->take(4) as $index => $item)
        <div class="media-item-wrapper js-lightbox-trigger" data-index="{{ $index }}">
            
            @if(str_starts_with($item->mime_type, 'video/'))
                <video src="{{ $item->file_path }}" class="media-element pointer-events-none" muted></video>
                <div class="video-play-overlay">
                    <i class="bi bi-play-fill fs-4"></i>
                </div>
            @else
                <img src="{{ $item->file_path }}" class="media-element pointer-events-none" alt="Media item">
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

@once
<style>
.media-grid { 
    display: grid; 
    gap: 4px; 
    border-radius: 0.5rem; 
    overflow: hidden; 
    max-height: 450px; 
    width: 100%; 
    /* Uses the dynamic aspect ratio passed from Blade, falling back to 16/9 */
    aspect-ratio: var(--grid-aspect-ratio, 16/9); 
}
.media-grid .media-item-wrapper { position: relative; overflow: hidden; cursor: zoom-in; }
.media-grid .media-item-wrapper:hover { opacity: 0.95; }
.media-grid .media-element { width: 100%; height: 100%; object-fit: cover; display: block; }

/* Grid Layouts */
.grid-1 { grid-template-columns: 1fr; }
.grid-2 { grid-template-columns: 1fr 1fr; }
.grid-3 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
.grid-3 > div:first-child { grid-row: 1 / 3; }
.grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }

/* Overlay UI elements cleaned out of inline HTML */
.video-play-overlay {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: #fff;
    background: rgba(0, 0, 0, 0.5);
    border-radius: 50%;
    width: 40px;
    height: 40px;
    display: flex;
    align-items: center;
    justify-content: center;
    pointer-events: none;
}

.media-more-overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(0, 0, 0, 0.6);
    pointer-events: none;
}
</style>

<script>
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('.js-lightbox-trigger');
    if (trigger && window.openGlobalLightbox) {
        const container = trigger.closest('.js-media-container');
        if (container) {
            const fullMedia = JSON.parse(container.dataset.fullMedia || '[]');
            window.openGlobalLightbox(fullMedia, trigger.dataset.index);
        }
    }
});
</script>
@endonce