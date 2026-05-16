@props(['isSpoiler' => false, 'parentIsSpoiler' => false])

<div class="spoiler-wrapper position-relative">
    @if($isSpoiler && !$parentIsSpoiler)
        <div class="spoiler-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded" style="z-index: 10;">
            <div class="text-center"><i class="bi bi-eye-slash fs-5 mb-1 d-block"></i><span class="fw-bold small">Spoiler Content</span></div>
        </div>
    @endif
    {{ $slot }}
</div>

@once
<style>
    .spoiler-wrapper .spoiler-overlay { transition: opacity 0.2s, visibility 0.2s; }
    .spoiler-wrapper:hover .spoiler-overlay { opacity: 0; visibility: hidden; pointer-events: none; }
</style>
@endonce
