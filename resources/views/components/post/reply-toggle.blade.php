@props(['post'])
    @if(!$post->is_locked && !$post->admin_locked)
        <button class="js-btn-reply btn btn-sm btn-light rounded-pill border-0 small"
            data-hub-type="{{ $post->hubable_type ?? $post->hub_type }}" 
            data-hub-id="{{ $post->hubable_id ?? $post->hub_id }}"
            data-parent-id="{{ $post->id }}">
            <i class="bi bi-reply me-1"></i>{{ __('posts.reply') }}
        </button>
    @else
    <button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled" disabled
        title="{{ __('posts.comment_locked') }}">
        <i class="bi bi-lock-fill me-1"></i>{{ $post->admin_locked ? __('posts.locked') : __('posts.disabled') }}
    </button>
    @endif
