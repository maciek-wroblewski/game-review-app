@props(['post'])

<div id="comment-list-{{ $post->id }}"
     class="js-comment-list-container overflow-hidden bg-white border-top border-light"
     style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;">
    <div class="p-3">
        <div class="js-replies-content">
            <div class="text-center text-muted small py-4">
                <i class="bi bi-chat-dots me-1"></i> Click to load replies
            </div>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('toggle-replies', async (e) => {
    const container = e.target;
    const btn = e.detail.btn;
    const contentArea = container.querySelector('.js-replies-content');
    const isOpen = container.dataset.open === 'true';
    const isLoaded = container.dataset.loaded === 'true';

    if (!isOpen) {
        // 1. Lazy Load if not already fetched
        if (!isLoaded) {
            contentArea.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-muted"></div></div>';
            try {
                const res = await fetch(`/posts/${btn.dataset.postId}/replies`, {
                    headers: { 'Accept': 'text/html', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (res.ok) {
                    contentArea.innerHTML = await res.text();
                    container.dataset.loaded = 'true';
                } else {
                    contentArea.innerHTML = '<div class="text-center text-danger small py-3">Failed to load replies</div>';
                }
            } catch (err) {
                contentArea.innerHTML = '<div class="text-center text-danger small py-3">Network error</div>';
            }
        }

        // 2. Animate Open
        container.offsetHeight; // Force reflow for smooth transition
        container.style.maxHeight = container.scrollHeight + 'px';
        container.style.opacity = '1';
        container.dataset.open = 'true';
        
        // Optional: Update button text
        btn.querySelector('.btn-text').textContent = 'Hide Replies';
        btn.querySelector('i').classList.replace('bi-chevron-down', 'bi-chevron-up');

    } else {
        // 3. Animate Close
        container.style.maxHeight = '0';
        container.style.opacity = '0';
        container.dataset.open = 'false';
        
        // Optional: Update button text
        btn.querySelector('.btn-text').textContent = 'Show Replies';
        btn.querySelector('i').classList.replace('bi-chevron-up', 'bi-chevron-down');
    }
});
</script>
@endonce
