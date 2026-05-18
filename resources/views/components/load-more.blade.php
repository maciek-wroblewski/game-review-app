@props([
'paginator',
'target',
'buttonClass' => 'btn btn-primary px-4 py-2 rounded-pill shadow-sm',
'text' => 'Load More'
])

@if($paginator && $paginator->hasMorePages())
<div class="text-center mt-4 js-load-more-wrapper" data-target-container="{{ $target }}">

    <button class="{{ $buttonClass }} js-load-more-btn" data-next-url="{{ $paginator->nextPageUrl() }}">
        {{ $text }}
    </button>

    <div class="js-load-more-spinner d-none spinner-border text-primary" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>

</div>
@endif

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
    document.addEventListener('click', function(e) {
        const button = e.target.closest('.js-load-more-btn');
        if (!button) return;

        const wrapper = button.closest('.js-load-more-wrapper');
        const selector = wrapper.dataset.targetContainer;
        let targetContainer = null;

        // 1. SMART SCOPING: If the target is a class selector, search contextually inside the closest component wrapper first.
        if (selector.startsWith('.')) {
            const context = wrapper.closest('.js-replies-wrapper') || wrapper.closest('.post-list-wrapper') || wrapper.parentElement;
            if (context) {
                targetContainer = context.querySelector(selector);
            }
        }
        
        // Fallback to global document lookup (perfect for distinct ID targets like #games-grid-wrapper)
        if (!targetContainer) {
            targetContainer = document.querySelector(selector);
        }

        const url = button.getAttribute('data-next-url');
        const spinner = wrapper.querySelector('.js-load-more-spinner');

        if (!url || !targetContainer) return;

        button.classList.add('d-none');
        spinner.classList.remove('d-none');

        fetch(url, {
            headers: { 
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json, text/html'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed loading content.');
            
            // 2. HYBRID PROTOCOL: Seamlessly parse either JSON structures or raw text views
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => ({ type: 'json', data }));
            } else {
                return response.text().then(html => ({ type: 'html', data: html }));
            }
        })
        .then(({ type, data }) => {
            let htmlContent = '';
            let nextPageUrl = null;

            if (type === 'json') {
                htmlContent = data.html;
                nextPageUrl = data.next_page_url;
            } else {
                htmlContent = data;
                try {
                    const urlObj = new URL(url, window.location.origin);
                    const currentScale = urlObj.searchParams.get('page') || '1';
                    const currentPage = parseInt(currentScale, 10);
                    urlObj.searchParams.set('page', (currentPage + 1).toString());
                    nextPageUrl = urlObj.toString();
                } catch (e) {
                    console.error('Error parsing pagination query string:', e);
                }
            }

            if (htmlContent && htmlContent.trim() !== '') {
                // FIX: Instead of raw string ingestion, convert HTML into active nodes
                const secondaryStage = document.createElement('div');
                secondaryStage.innerHTML = htmlContent.trim();
                
                // Extract all primary elements from the payload response payload
                const incomingNodes = Array.from(secondaryStage.children);
                
                incomingNodes.forEach(node => {
                    // Inject the fade class programmatically to guarantee transition execution on mount
                    node.classList.add('animate-fade-in');
                    targetContainer.appendChild(node);
                });
            } else if (type === 'html') {
                nextPageUrl = null;
            }

            if (nextPageUrl) {
                button.setAttribute('data-next-url', nextPageUrl);
                button.classList.remove('d-none');
                spinner.classList.add('d-none');
            } else {
                wrapper.remove();
            }
        })
        .catch(error => {
            console.error('Pagination Error:', error);
            button.classList.remove('d-none');
            spinner.classList.add('d-none');
        });
    });
});
</script>
@endonce