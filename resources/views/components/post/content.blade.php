@props(['post'])
@php
$visualMedia = $post->media->filter(fn($m) => in_array($m->mime_type, ['image/jpeg', 'image/png', 'image/gif', 'video/mp4']))->values();
$mediaCount = $visualMedia->count();
$gridClass = match($mediaCount) { 0, 1 => 'grid-1', 2 => 'grid-2', 3 => 'grid-3', default => 'grid-4' };
$lightboxPayload = $visualMedia->map(fn($m) => ['url' => $m->file_path, 'type' => str_starts_with($m->mime_type, 'video/') ? 'video' : 'image'])->toJson();
@endphp

@once
<style>
    .text-truncate-container {
        display: -webkit-box;
        -webkit-line-clamp: var(--line-clamp, 3);
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .cursor-crosshair {
        cursor: crosshair;
    }

    .spoiler-overlay {
        z-index: 10;
        backdrop-filter: blur(5px);
        transition: opacity 0.2s, visibility 0.2s;
    }

    .spoiler-container:hover .spoiler-overlay {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
    
    .media-grid {
        display: grid;
        gap: 4px;
        border-radius: 0.5rem;
        overflow: hidden;
        max-height: 450px;
        aspect-ratio: 16/9;
        width: 100%; /* ← ADD THIS */
    }


    .media-grid .media-item-wrapper {
        position: relative;
        overflow: hidden;
        cursor: zoom-in;
    }

    .media-grid .media-item-wrapper:hover {
        opacity: 0.95;
    }

    .media-grid .media-element {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
    }

    .grid-1 {
        grid-template-columns: 1fr;
    }

    .grid-2 {
        grid-template-columns: 1fr 1fr;
    }

    .grid-3 {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
    }

    .grid-3>div:first-child {
        grid-row: 1 / 3;
    }

    .grid-4 {
        grid-template-columns: 1fr 1fr;
        grid-template-rows: 1fr 1fr;
    }

    .media-edit-item:hover {
        filter: brightness(0.8);
        cursor: pointer;
    }

    .pointer-events-none {
        pointer-events: none;
    }
</style>
@endonce

<div class="js-view-mode card-body pt-2 position-relative spoiler-container" data-media-count="{{ $mediaCount }}">
    @if($post->is_spoiler)
    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded spoiler-overlay">
        <div class="text-center"><i class="bi bi-eye-slash fs-3 mb-2 d-block"></i><span class="fw-bold">Spoiler Content</span></div>
    </div>
    @endif

    <div class="js-view-mode">
        @if($post->parentPost)
        <div class="border rounded p-3 mb-3 bg-light d-flex flex-column" style="cursor: pointer;" onclick="window.location.href='/posts/{{ $post->parentPost->id }}'">
            <div class="d-flex align-items-center mb-1">
                <img src="{{ $post->parentPost->author->avatar }}" class="rounded-circle me-2" style="width: 20px; height: 20px;">
                <span class="fw-bold small me-1">{{ $post->parentPost->author->username }}</span>
                <span class="text-muted small">· {{ $post->parentPost->created_at->shortAbsoluteDiffForHumans() }}</span>
            </div>
            <p class="mb-0 small text-truncate">{{ $post->parentPost->body }}</p>
        </div>
        @endif

        <div class="js-text-wrapper">
            <p class="js-text-body card-text fs-5 mb-3" style="white-space: pre-line;">{{ $post->body }}</p>
        </div>

        {{-- Button is hidden by default; JS will show it conditionally --}}
        <button class="js-btn-read-more btn btn-link text-decoration-none p-0 mb-3 fw-bold" style="display: none;">Read more...</button>

        <div class="js-media-container mb-3" data-full-media="{{ $lightboxPayload }}">
            @if($mediaCount > 0)
            <div class="media-grid {{ $gridClass }} shadow-sm w-100">
                @foreach($visualMedia->take(4) as $index => $media)
                <div class="media-item-wrapper js-lightbox-trigger" data-index="{{ $index }}">
                    @if(str_starts_with($media->mime_type, 'video/'))
                    <video src="{{ $media->file_path }}" class="media-element pointer-events-none" muted></video>
                    <div class="position-absolute top-50 start-50 translate-middle text-white pointer-events-none" style="background: rgba(0,0,0,0.5); border-radius: 50%; width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                        <i class="bi bi-play-fill fs-4"></i>
                    </div>
                    @else
                    <img src="{{ $media->file_path }}" class="media-element pointer-events-none">
                    @endif

                    @if($mediaCount > 4 && $index === 3)
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center pointer-events-none" style="background: rgba(0,0,0,0.6);"><span class="text-white fs-2 fw-bold">+{{ $mediaCount - 4 }}</span></div>
                    @endif
                </div>
                @endforeach
            </div>
            @endif
        </div>

        @if($post->parent_id)
        <x-post.quote :post="$post->parent" :parentIsSpoiler="$post->is_spoiler" />
        @endif
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        // 1. Dynamic Read More Logic based on lines & media
        document.querySelectorAll('.js-view-mode').forEach(card => {
            const wrapper = card.querySelector('.js-text-wrapper');
            const btn = card.querySelector('.js-btn-read-more');
            if (!wrapper || !btn) return;

            const mediaCount = parseInt(card.dataset.mediaCount) || 0;
            const p = wrapper.querySelector('.js-text-body');
            if (!p) return;

            // Use requestAnimationFrame to ensure accurate layout measurements
            requestAnimationFrame(() => {
                const lineHeight = parseFloat(window.getComputedStyle(p).lineHeight) || 24;
                const scrollHeight = p.scrollHeight;
                const lineCount = Math.round(scrollHeight / lineHeight);

                let shouldClamp = false;
                let clampLines = 3;

                if (mediaCount > 0 && lineCount > 1) {
                    shouldClamp = true;
                    clampLines = 1; // Clamp to 1 line when media exists
                } else if (mediaCount === 0 && lineCount > 5) {
                    shouldClamp = true;
                    clampLines = 5; // Clamp to 5 lines when no media
                }

                if (shouldClamp) {
                    wrapper.classList.add('text-truncate-container');
                    wrapper.style.setProperty('--line-clamp', clampLines);
                    btn.style.display = 'block';
                } else {
                    wrapper.classList.remove('text-truncate-container');
                    btn.style.display = 'none';
                }
            });
        });

        // 2. Click Handlers (Lightbox & Expand)
        document.addEventListener('click', (e) => {
            const card = e.target.closest('.js-view-mode');
            if (!card) return;

            // Lightbox functionality 
            const trigger = e.target.closest('.js-lightbox-trigger');
            if (trigger && window.openGlobalLightbox) {
                const container = trigger.closest('.js-media-container');
                if (container) {
                    const fullMediaArray = JSON.parse(container.dataset.fullMedia);
                    window.openGlobalLightbox(fullMediaArray, trigger.dataset.index);
                }
            }

            // Read More Text Expansion Engine
            if (e.target.closest('.js-btn-read-more')) {
                const btn = e.target.closest('.js-btn-read-more');
                const textWrapper = card.querySelector('.js-text-wrapper');
                if (!textWrapper) return;

                const startHeight = textWrapper.offsetHeight;
                textWrapper.classList.remove('text-truncate-container'); 
                btn.style.display = 'none';
                const endHeight = textWrapper.offsetHeight;
                
                const anim = textWrapper.animate([
                    { height: `${startHeight}px`, overflow: 'hidden' },
                    { height: `${endHeight}px`, overflow: 'hidden' }
                ], { duration: 300, easing: 'ease-in-out' });

                anim.onfinish = () => {
                    textWrapper.style.height = '';
                    textWrapper.style.overflow = '';
                };
            }
        });
    });
</script>
@endonce
