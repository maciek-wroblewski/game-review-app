@props(['isSpoiler' => false])

@if($isSpoiler)
<div class="spoiler-container position-relative d-inline-block w-100" data-spoiler>
    <div
        class="spoiler-overlay position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded">
        <div class="d-flex flex-row align-items-center gap-2">
            <i class="bi bi-eye-slash fs-3"></i>
            <span class="fw-bold">{{ __('posts.spoiler_content') }}</span>
        </div>
    </div>

    {{ $slot }}
</div>
@else
{{ $slot }}
@endif
