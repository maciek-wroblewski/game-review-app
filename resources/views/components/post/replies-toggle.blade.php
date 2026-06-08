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

@once
<script>
document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-btn-show-replies');
    if (!btn) return;

    // 1. Go UP to find the shared parent card/wrapper
    const postWrapper = btn.closest('.js-post-wrapper');
    if (!postWrapper) return;

    // 2. Go DOWN to find the specific comment list inside this wrapper
    const container = postWrapper.querySelector('.js-comment-list-container');
    if (!container) return;

    e.preventDefault();
    e.stopPropagation();

    // 3. Dispatch your custom event!
    container.dispatchEvent(new CustomEvent('toggle-replies', { 
        bubbles: true, 
        detail: { btn } 
    }));
});
</script>
@endonce