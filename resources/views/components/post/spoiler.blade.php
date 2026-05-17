@props(['isSpoiler' => false, 'parentIsSpoiler' => false])
@if($isSpoiler && !$parentIsSpoiler)
<div class="spoiler-container position-relative">
    <div class="spoiler-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded">
        <div class="text-center"><i class="bi bi-eye-slash fs-3 mb-2 d-block"></i><span class="fw-bold">Spoiler Content</span></div>
    </div>
    {{ $slot }}
</div>
@else
    {{ $slot }}
@endif

@once
<style>
.spoiler-container { position: relative; }
.spoiler-overlay { z-index: 10; backdrop-filter: blur(5px); transition: opacity 0.2s, visibility 0.2s; pointer-events: none; }
.spoiler-container:hover .spoiler-overlay { opacity: 0; visibility: hidden; }
</style>
@endonce
