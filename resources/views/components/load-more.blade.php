@props(['paginator', 'target'])

@if($paginator && $paginator->hasMorePages())
    <div class="text-center mt-4 js-load-more-wrapper" data-target-container="{{ $target }}">
        
        <button class="btn btn-primary px-4 py-2 rounded-pill shadow-sm js-load-more-btn"
                data-next-url="{{ $paginator->nextPageUrl() }}">
            Load More
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
        const targetContainer = document.querySelector(wrapper.dataset.targetContainer);
        const spinner = wrapper.querySelector('.js-load-more-spinner');
        const url = button.getAttribute('data-next-url');

        if (!url || !targetContainer) return;

        button.classList.add('d-none');
        spinner.classList.remove('d-none');

        fetch(url, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => {
            if (!response.ok) throw new Error('Failed loading content.');
            return response.json(); // Standardized to always parse JSON responses
        })
        .then(data => {
            if (data.html && data.html.trim() !== '') {
                targetContainer.insertAdjacentHTML('beforeend', data.html);
            }

            // If the server provides a subsequent page token, update the target; otherwise kill the loader wrapper
            if (data.next_page_url) {
                button.setAttribute('data-next-url', data.next_page_url);
                button.classList.remove('d-none');
                spinner.classList.add('d-none');
            } else {
                wrapper.remove();
            }
        })
        .catch(error => {
            console.error('Loader error:', error);
            button.classList.remove('d-none');
            spinner.classList.add('d-none');
        });
    });
});
</script>
@endonce