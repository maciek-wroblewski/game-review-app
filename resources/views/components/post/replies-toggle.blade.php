@props(['post'])
@if ($post->replies()->count() > '0')
<button class="js-btn-show-replies btn btn-sm btn-light rounded-pill border-0 small" data-post-id="{{ $post->id }}"
    data-target-container="comment-list-{{ $post->id }}">
    <i class="bi bi-chevron-down me-1"></i>
    <span class="btn-text">Show Replies</span>
</button>
@else
<button class="btn btn-sm btn-light rounded-pill border-0 small text-muted disabled" disabled
    title="This post has no comments">
    <i class="bi bi-ban me-1"></i>No Replies
</button>
@endif

@once
<script>
    document.addEventListener('click', (e) => {
    const btn = e.target.closest('.js-btn-show-replies');
    if (!btn) return;

    const targetId = btn.dataset.targetContainer;
    if (!targetId) return;

    const container = document.getElementById(targetId);
    if (!container) return;

    e.preventDefault();
    e.stopPropagation();

    // Dispatch a custom event so the container handles its own logic
    container.dispatchEvent(new CustomEvent('toggle-replies', { 
        bubbles: true, 
        detail: { btn } 
    }));
});
</script>
@endonce