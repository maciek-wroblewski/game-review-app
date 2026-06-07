@props(['size' => 2])

<div class="truncate-wrapper" data-js="truncate-text" style="--lines: {{ $size }};">
    <div class="truncate-content">
        {{ $slot }}
    </div>
    <button type="button" class="btn btn-link p-0 mt-1 truncate-btn d-none">{{ __('common.read_more') }}</button>
</div>