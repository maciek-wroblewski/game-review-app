@props(['post'])
@if(!$post->trashed())
<div class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
    <button 
        class="js-btn-reply btn btn-light rounded-pill border shadow-sm d-flex align-items-center gap-2"
        data-hub-type="{{ $post->hubable_type }}"
        data-hub-id="{{ $post->hubable_id }}"
        data-parent-id="{{ $post->id }}">
        <i class="bi bi-chat"></i> Reply
    </button>

    <x-like-button :post="$post" />
</div>

{{-- Slide-down container for the comment form --}}
<div class="js-reply-container overflow-hidden" style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;">
    <x-post.comment-create 
        :hubType="$post->hub_type" 
        :hubId="$post->hub_id" 
        :parentId="$post->id" 
    />
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
