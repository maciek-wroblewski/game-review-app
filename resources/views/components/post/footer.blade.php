@props(['post'])
@if(!$post->trashed())

<div class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
    <div class="d-flex gap-2 mt-2">
        {{-- 1. The Reply Button --}}
        @if(!$post->is_locked)
        <button class="js-btn-reply btn btn-sm btn-light rounded-pill border-0 small"
            data-hub-type="{{ $post->hubable_type }}" data-hub-id="{{ $post->hubable_id }}"
            data-parent-id="{{ $post->id }}">
            <i class="bi bi-reply me-1"></i>Reply
        </button>
        @else
        {{-- Disabled State for Locked Posts --}}
        <button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled" disabled
            title="This comment is locked">
            <i class="bi bi-lock-fill me-1"></i>Locked
        </button>
        @endif

        <button class="js-btn-show-replies btn btn-sm btn-light rounded-pill border-0 small">
            <i class="bi bi-chevron-down me-1"></i>Show Replies
        </button>
    </div>
    <x-like-button :post='$post' />
</div>

{{-- Slide-down container for the comment form --}}
<div class="js-reply-container overflow-hidden"
    style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;">
    <x-post.comment-create :hubType="$post->hub_type" :hubId="$post->hub_id" :parentId="$post->id" />
</div>
@endif

@once
<script>
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-btn-reply');
        if (!btn) return;

        // Find the sibling container
        const container = btn.closest('.js-post-footer').nextElementSibling;
        if (!container || !container.classList.contains('js-reply-container')) return;

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