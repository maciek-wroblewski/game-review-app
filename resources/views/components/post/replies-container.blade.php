@props(['postId'])

<div class="js-comment-list-container overflow-hidden bg-white border-top border-light"
     data-post-id="{{ $postId }}"
     data-open="false"
     data-loaded="false"
     style="max-height: 0; opacity: 0; transition: max-height 0.3s ease-out, opacity 0.3s ease-out;"
     {{ $attributes }}>
    <div class="p-3">
        {{ $slot }}
    </div>
</div>

@once
<script>
document.addEventListener('toggle-replies', async (e) => {
    const container = e.target;
    const btn = e.detail?.btn; // Optional chaining prevents crashes if btn is undefined
    const contentArea = container.querySelector('.js-replies-content');
    
    if (!contentArea) return;

    // Safety fallback initialization for state trackers
    if (!container.dataset.open) container.dataset.open = 'false';
    if (!container.dataset.loaded) container.dataset.loaded = 'false';

    const isOpen = container.dataset.open === 'true';
    const isLoaded = container.dataset.loaded === 'true';

    // --- ANIMATION: OPENING ---
    if (!isOpen) {
        if (!isLoaded) {
            contentArea.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-muted"></div></div>';
            
            try {
                // Fetch using the container's own post ID attribute (much safer than the button)
                const postId = container.dataset.postId;
                const res = await fetch(`/posts/${postId}/replies`, {
                    headers: { 
                        'Accept': 'application/json, text/html', 
                        'X-Requested-With': 'XMLHttpRequest' 
                    }
                });
                
                if (res.ok) {
                    const contentType = res.headers.get('content-type');
                    
                    // Support both JSON payloads and plain HTML payloads seamlessly
                    if (contentType && contentType.includes('application/json')) {
                        const data = await res.json();
                        contentArea.innerHTML = data.html;
                        
                        // Update load-more pagination values if applicable
                        const loadMoreBtn = container.querySelector('.js-load-more-btn');
                        const loadMoreWrapper = container.querySelector('.js-load-more-wrapper');
                        if (loadMoreBtn && loadMoreWrapper && data.next_page_url) {
                            loadMoreBtn.setAttribute('data-next-url', data.next_page_url);
                            loadMoreWrapper.classList.remove('d-none');
                        }
                    } else {
                        contentArea.innerHTML = await res.text();
                    }
                    container.dataset.loaded = 'true';
                } else {
                    contentArea.innerHTML = '<div class="text-center text-danger small py-3">Failed to load replies</div>';
                }
            } catch (err) {
                console.error('Error fetching replies:', err);
                contentArea.innerHTML = '<div class="text-center text-danger small py-3">Network error</div>';
            }
        }

        container.offsetHeight; // Force layout reflow
        container.style.maxHeight = container.scrollHeight + 'px';
        container.style.opacity = '1';
        container.dataset.open = 'true';
        
        if (btn) {
            const btnText = btn.querySelector('.btn-text');
            if (btnText) btnText.textContent = 'Hide Replies';
            const icon = btn.querySelector('i');
            if (icon) icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
        }

        container.addEventListener('transitionend', function handler(event) {
            if (event.propertyName === 'max-height' && container.dataset.open === 'true') {
                container.style.maxHeight = 'none';
            }
            container.removeEventListener('transitionend', handler);
        });

    // --- ANIMATION: CLOSING ---
    } else {
        container.style.maxHeight = container.scrollHeight + 'px';
        container.offsetHeight; // Force layout reflow

        container.style.maxHeight = '0';
        container.style.opacity = '0';
        container.dataset.open = 'false';
        
        if (btn) {
            const btnText = btn.querySelector('.btn-text');
            if (btnText) btnText.textContent = 'Show Replies';
            const icon = btn.querySelector('i');
            if (icon) icon.classList.replace('bi-chevron-up', 'bi-chevron-down');
        }
    }
});
</script>
@endonce