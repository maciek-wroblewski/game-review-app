@props(['media'])
@php
    $visualMedia = $media->filter(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']))->values();
    $count = $visualMedia->count();
    $gridClass = match($count) { 0 => 'grid-1', 1 => 'grid-1', 2 => 'grid-2', 3 => 'grid-3', default => 'grid-4' };
    $payload = $visualMedia->map(fn($m) => ['url' => $m->file_path, 'type' => str_starts_with($m->mime_type, 'video/') ? 'video' : 'image'])->toJson();
@endphp

@if($count > 0)
<div class="js-media-container mt-2" data-full-media="{{ $payload }}">
    <div class="media-grid {{ $gridClass }} shadow-sm w-100">
        @foreach($visualMedia->take(4) as $index => $item)
        <div class="media-item-wrapper js-lightbox-trigger position-relative" data-index="{{ $index }}">
            @if(str_starts_with($item->mime_type, 'video/'))
                <video src="{{ $item->file_path }}" class="media-element w-100 h-100 object-fit-cover" muted></video>
                <div class="position-absolute top-50 start-50 translate-middle text-white bg-dark bg-opacity-50 rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;"><i class="bi bi-play-fill"></i></div>
            @else
                <img src="{{ $item->file_path }}" class="media-element w-100 h-100 object-fit-cover">
            @endif
            @if($count > 4 && $index === 3)
                <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-60"><span class="text-white fs-2 fw-bold">+{{ $count - 4 }}</span></div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

@once
<style>
    .media-grid { display: grid; gap: 4px; border-radius: 0.5rem; overflow: hidden; max-height: 300px; aspect-ratio: 16/9; width: 100%; }
    .grid-1 { grid-template-columns: 1fr; }
    .grid-2 { grid-template-columns: 1fr 1fr; }
    .grid-3 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
    .grid-3 > div:first-child { grid-row: 1 / 3; }
    .grid-4 { grid-template-columns: 1fr 1fr; grid-template-rows: 1fr 1fr; }
    .media-element { display: block; object-fit: cover; }
</style>
@endonce
