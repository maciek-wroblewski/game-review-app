@props([
    'spoilerClass' => 'js-create-spoiler',
    'lockClass' => 'js-create-locked',
    'spoilerId' => 'spoiler-1',
    'lockId' => 'lock-1',
    'spoilerChecked' => false,
    'lockChecked' => false
])
<div class="d-flex gap-3">
    <div class="form-check form-switch mb-0">
        <input class="form-check-input {{ $spoilerClass }}" type="checkbox" id="{{ $spoilerId }}" {{ $spoilerChecked ? 'checked' : '' }}>
        <label class="form-check-label small user-select-none" for="{{ $spoilerId }}">
            <i class="bi bi-eye-slash-fill text-warning me-1"></i> {{ __('posts.spoiler') }}
        </label>
    </div>
    <div class="form-check form-switch mb-0">
        <input class="form-check-input {{ $lockClass }}" type="checkbox" id="{{ $lockId }}" {{ $lockChecked ? 'checked' : '' }}>
        <label class="form-check-label small user-select-none" for="{{ $lockId }}">
            <i class="bi bi-lock-fill text-danger me-1"></i> {{ __('posts.lock') }}
        </label>
    </div>
</div>
