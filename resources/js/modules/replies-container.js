export default class RepliesContainer {
    constructor() {
        document.addEventListener('toggle-replies', async (e) => {
            const container = e.target;
            const btn = e.detail?.btn; 
            const contentArea = container.querySelector('.js-replies-content');
            
            if (!contentArea) return;

            if (!container.dataset.open) container.dataset.open = 'false';
            if (!container.dataset.loaded) container.dataset.loaded = 'false';

            const isOpen = container.dataset.open === 'true';
            const isLoaded = container.dataset.loaded === 'true';

            // --- ANIMATION: OPENING ---
            if (!isOpen) {
                if (!isLoaded) {
                    contentArea.innerHTML = '<div class="text-center py-3"><div class="spinner-border spinner-border-sm text-muted"></div></div>';
                    
                    try {
                        const postId = container.dataset.postId;
                        const res = await fetch(`/posts/${postId}/replies`, {
                            headers: { 
                                'Accept': 'application/json, text/html', 
                                'X-Requested-With': 'XMLHttpRequest' 
                            }
                        });
                        
                        if (res.ok) {
                            const contentType = res.headers.get('content-type');
                            
                            if (contentType && contentType.includes('application/json')) {
                                const data = await res.json();
                                contentArea.innerHTML = data.html;
                                
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

                // Apply the 60vh height directly
                container.offsetHeight; // Force layout reflow
                container.style.maxHeight = '60vh';
                container.style.opacity = '1';
                container.dataset.open = 'true';
                
                if (btn) {
                    const btnText = btn.querySelector('.btn-text');
                    if (btnText) btnText.textContent = 'Hide Replies';
                    const icon = btn.querySelector('i');
                    if (icon) icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
                }

            // --- ANIMATION: CLOSING ---
            } else {
                // Drop straight down to 0vh
                container.style.maxHeight = '0vh';
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
    }
}
