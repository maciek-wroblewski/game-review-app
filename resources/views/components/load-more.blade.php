@props([
'paginator',
'target',
'buttonClass' => 'btn btn-primary px-4 py-2 rounded-pill shadow-sm',
'text' => __('common.load_more')
])

@if($paginator && $paginator->hasMorePages())
<div class="text-center mt-4 js-load-more-wrapper" data-target-container="{{ $target }}">

    <button class="{{ $buttonClass }} js-load-more-btn" data-next-url="{{ $paginator->nextPageUrl() }}">
        {{ $text }}
    </button>

    <div class="js-load-more-spinner d-none spinner-border text-primary" role="status">
        <span class="visually-hidden">{{ __('common.loading') }}</span>
    </div>

</div>
@endif
