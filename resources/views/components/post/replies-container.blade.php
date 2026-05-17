@props(['post'])

<div class="js-comment-list-container overflow-hidden bg-white border-top border-light"
     style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;">
    <div class="p-3">
        {{-- Added max-height and overflow-y-auto here --}}
        <div class="js-replies-content overflow-y-auto overflow-x-hidden" style="max-height: 60vh;">
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

        // Animate Open
        container.offsetHeight; // Force reflow
        container.style.maxHeight = container.scrollHeight + 'px';
        container.style.opacity = '1';
        container.dataset.open = 'true';
        
        btn.querySelector('.btn-text').textContent = 'Hide Replies';
        btn.querySelector('i').classList.replace('bi-chevron-down', 'bi-chevron-up');

        // NEW: Once the animation is done, remove the max-height limit. 
        // This allows the div to grow/shrink dynamically if content inside changes!
        container.addEventListener('transitionend', function handler(event) {
            if (event.propertyName === 'max-height' && container.dataset.open === 'true') {
                container.style.maxHeight = 'none';
            }
            container.removeEventListener('transitionend', handler);
        });

    } else {
        // NEW: Before we can animate closed, we must swap 'none' back to explicit pixels.
        container.style.maxHeight = container.scrollHeight + 'px';
        
        // Force reflow so the browser registers the pixel value
        container.offsetHeight; 

        // Animate Closed
        container.style.maxHeight = '0';
        container.style.opacity = '0';
        container.dataset.open = 'false';
        
        btn.querySelector('.btn-text').textContent = 'Show Replies';
        btn.querySelector('i').classList.replace('bi-chevron-up', 'bi-chevron-down');
    }
});
</script>
@endonce