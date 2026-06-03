@props(['post'])

<div class="js-replies-container">
    @if (($post->replies_count ?? $post->replies()->count()) > 0)
        <button class="js-btn-show-replies btn btn-sm btn-light rounded-pill border-0 small"
            data-post-id="{{ $post->id }}">
            <i class="bi bi-chevron-down me-1"></i>
            <span class="btn-text">{{ __('posts.show_replies') }}</span>
        </button>
    @else
        <button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled" disabled
            title="{{ __('posts.no_comments') }}">
            <i class="bi bi-ban me-1"></i>{{ __('posts.no_replies') }}
        </button>
    @endif
</div>
