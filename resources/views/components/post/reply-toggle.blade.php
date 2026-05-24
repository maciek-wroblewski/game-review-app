@props(['post'])
    @if(!$post->is_locked && !$post->admin_locked)
        @auth
        <button class="js-btn-reply btn btn-sm btn-light rounded-pill border-0 small"
            data-hub-type="{{ $post->hubable_type ?? $post->hub_type }}" 
            data-hub-id="{{ $post->hubable_id ?? $post->hub_id }}"
            data-parent-id="{{ $post->id }}">
            <i class="bi bi-reply me-1"></i>Reply
        </button>
        @else
        <a href="{{ route('login') }}" class="btn btn-sm btn-light rounded-pill border-0 small"
            title="Please log in to reply">
            <i class="bi bi-reply me-1"></i>Reply
        </a>
        @endauth
    @else
    <button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled" disabled
        title="This comment is locked">
        <i class="bi bi-lock-fill me-1"></i>{{ $post->admin_locked ? 'Locked' : 'Disabled' }}
    </button>
    @endif

@once
<script>
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-btn-reply');
        if (!btn) return;

        const postWrapper = btn.closest('.js-post-wrapper');
        if (!postWrapper) return;

        // 2. Go DOWN to find the specific container inside THIS wrapper
        const container = postWrapper.querySelector('.js-reply-container');
        if (!container) return;

        e.preventDefault();
        e.stopPropagation();

        const isOpen = container.dataset.open === 'true';

        if (!isOpen) {
            container.style.maxHeight = container.scrollHeight + 'px';
            container.style.opacity = '1';
            container.dataset.open = 'true';
        } else {
            container.style.maxHeight = '0';
            container.style.opacity = '0';
            container.dataset.open = 'false';
        }
    });
</script>
@endonce