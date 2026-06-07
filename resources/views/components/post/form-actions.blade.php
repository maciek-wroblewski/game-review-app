@props([
    'clearClass' => 'js-btn-create-clear',
    'submitClass' => 'js-btn-submit-post',
    'spinnerClass' => 'js-submit-spinner',
    'clearLabel' => __('posts.clear'),
    'submitLabel' => __('posts.post_submit')
])
<div class="d-flex gap-2 justify-content-end">
    <button class="{{ $clearClass }} btn btn-outline-secondary btn-sm">{{ $clearLabel }}</button>
    <button class="{{ $submitClass }} btn btn-primary btn-sm px-3">
        <span class="{{ $spinnerClass }} spinner-border spinner-border-sm d-none"></span> {{ $submitLabel }}
    </button>
</div>
