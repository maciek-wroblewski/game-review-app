@props(['isSpoiler' => false])

@if($isSpoiler)
<div class="spoiler-container position-relative d-inline-block w-100" data-spoiler>
    <div
        class="spoiler-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded">
        <div class="d-flex flex-row align-items-center gap-2">
            <i class="bi bi-eye-slash fs-3"></i>
            <span class="fw-bold">Spoiler Content</span>
        </div>
    </div>

    {{ $slot }}
</div>
@else
{{ $slot }}
@endif

@once
<style>
    .spoiler-container {
        position: relative;
        isolation: isolate;
    }

    .spoiler-overlay {
        /* 
       A low positive z-index is now all we need to sit on top of 
       the immediate text and nested containers inside this context.
    */
        z-index: 3;
        backdrop-filter: blur(5px);
        transition: opacity 0.2s ease, visibility 0.2s ease;
        pointer-events: auto;
    }

    /* Only reveal the immediate overlay layer */
    .spoiler-container.is-revealed>.spoiler-overlay {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
    const attachSpoilerListeners = () => {
        document.querySelectorAll('[data-spoiler]:not([data-spoiler-initialized])').forEach(spoiler => {
            // Mark as initialized to prevent duplicate event listeners
            spoiler.setAttribute('data-spoiler-initialized', 'true');

            // mouseenter does not bubble up to parents
            spoiler.addEventListener('mouseenter', () => {
                spoiler.classList.add('is-revealed');
            });

            // mouseleave does not bubble up to parents
            spoiler.addEventListener('mouseleave', () => {
                spoiler.classList.remove('is-revealed');
            });
        });
    };

    // Initial run
    attachSpoilerListeners();

    // Optional: Re-initialize if you use dynamic frontend frameworks like Livewire
    document.addEventListener('livewire:navigated', attachSpoilerListeners);
});
</script>
@endonce