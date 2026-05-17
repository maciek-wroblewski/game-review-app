@props(['media' => [], 'lightboxPayload' => '[]'])
@php
$visualMedia = $media->filter(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']))->values();
$count = $visualMedia->count();
$gridClass = match($count) { 0 => '', 1 => 'grid-1', 2 => 'grid-2', 3 => 'grid-3', default => 'grid-4' };
@endphp

@if($count > 0)
<div class="js-media-container mb-3" data-full-media="{{ $lightboxPayload }}">
    <div class="media-grid {{ $gridClass }} shadow-sm w-100">
        @foreach($visualMedia->take(4) as $index => $item)
        <div class="media-item-wrapper js-lightbox-trigger" data-index="{{ $index }}">
            @if(str_starts_with($item->mime_type, 'video/'))
            <video src="{{ $item->file_path }}" class="media-element pointer-events-none" muted></video>
            <div class="position-absolute top-50 start-50 translate-middle text-white pointer-events-none" style="background: rgba(0,0,0,0.5); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                <i class="bi bi-play-fill fs-4"></i>
            </div>
            @else
            <img src="{{ $item->file_path }}" class="media-element pointer-events-none">
            @endif
            @if($count > 4 && $index === 3)
            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center pointer-events-none" style="background: rgba(0,0,0,0.6);">
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
.media-grid { display: grid; gap: 4px; border-radius: 0.5rem; overflow: hidden; max-height: 450px; aspect-ratio: 16/9; width: 100%; }
.media-grid .media-item-wrapper { position: relative; overflow: hidden; cursor: zoom-in; }
.media-grid .media-item-wrapper:hover { opacity: 0.95; }
.media-grid .media-element { width: 100%; height: 100%; object-fit: cover; display: block; }
.grid-1 { grid-template-columns: 1fr; }
.grid-2 { grid-template-columns: 1fr 1fr; }
.grid-3 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
.grid-3>div:first-child { grid-row: 1 / 3; }
.grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
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
