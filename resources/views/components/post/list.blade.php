@props(['posts'])

<div class="post-list-wrapper">
    {{-- The Container where new posts will be appended --}}
    <div id="post-container" class="mb-4">
        <x-post.items :posts="$posts" />
    </div>

    {{-- The Load More Button --}}
    @if($posts->hasMorePages())
        <div class="text-center">
            <button 
                id="load-more-btn" 
                class="btn btn-outline-primary w-100"
                data-next-url="{{ $posts->nextPageUrl() }}">
                Load More
            </button>
            
            <div id="load-more-spinner" class="spinner-border text-primary d-none" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>
    @endif
</div>

{{-- Vanilla JS for AJAX Loading --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const loadMoreBtn = document.getElementById('load-more-btn');
        const spinner = document.getElementById('load-more-spinner');
        const container = document.getElementById('post-container');

        if(loadMoreBtn) {
            loadMoreBtn.addEventListener('click', function() {
                const url = this.getAttribute('data-next-url');
                if(!url) return;

                // UI Loading State
                loadMoreBtn.classList.add('d-none');
                spinner.classList.remove('d-none');

                // Fetch next page
                fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest' // Tells Laravel it's an AJAX request
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Append the new HTML to our container
                    container.insertAdjacentHTML('beforeend', html);

                    // Parse the new HTML to check if there's another "next page" URL
                    // We extract it from standard Laravel pagination logic
                    const urlParams = new URLSearchParams(url.split('?')[1]);
                    const currentPage = parseInt(urlParams.get('page'));
                    
                    // Update button for the NEXT page
                    const nextUrl = url.replace(`page=${currentPage}`, `page=${currentPage + 1}`);
                    
                    // Let's assume you have a max page logic, but generally, 
                    // if HTML returned empty, we hide the button.
                    if(html.trim() === '') {
                        loadMoreBtn.remove();
                    } else {
                        loadMoreBtn.setAttribute('data-next-url', nextUrl);
                        loadMoreBtn.classList.remove('d-none');
                    }
                    spinner.classList.add('d-none');
                })
                .catch(error => {
                    console.error('Error loading more posts:', error);
                    loadMoreBtn.classList.remove('d-none');
                    spinner.classList.add('d-none');
                });
            });
        }
    });
</script>