@props(['post'])

@if(!$post->is_locked)
<button class="js-btn-reply btn btn-sm btn-light rounded-pill border-0 small"
    data-hub-type="{{ $post->hubable_type ?? $post->hub_type }}" data-hub-id="{{ $post->hubable_id ?? $post->hub_id }}"
    data-parent-id="{{ $post->id }}" data-target-container="reply-container-{{ $post->id }}">
    <i class="bi bi-reply me-1"></i>Reply
</button>
@else
<button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled" disabled
    title="This comment is locked">
    <i class="bi bi-lock-fill me-1"></i>Locked
</button>
@endif

{{-- JS lives here, but targets containers by ID --}}
@once
<script>
    document.addEventListener('click', (e) => {
        const btn = e.target.closest('.js-btn-reply');
        if (!btn) return;

        const targetId = btn.dataset.targetContainer;
        if (!targetId) return;

        const container = document.getElementById(targetId);
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