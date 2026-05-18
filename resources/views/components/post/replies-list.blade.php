@props(['replies' => null])

<div class="js-replies-wrapper">
    <!-- The content container holding the comments -->
    <div class="js-replies-content overflow-y-auto overflow-x-hidden" style="max-height: 60vh;">
        @if($replies)
            {{-- Direct Mode: Render items immediately on first load --}}
            <x-post.replies-items :replies="$replies" />
        @else
            {{-- Lazy-loaded Accordion Mode: Placeholder until expanded --}}
            <div class="text-center text-muted small py-4">
                <i class="bi bi-chat-dots me-1"></i> Click to load replies
            </div>
        @endif
    </div>

    <!-- "Load More" Pagination Wrapper -->
    <div class="text-center mt-2 {{ ($replies && $replies->hasMorePages()) ? '' : 'd-none' }} js-load-more-wrapper">
        <button class="btn btn-sm btn-outline-primary w-100 js-load-more-btn" data-next-url="{{ $replies ? $replies->nextPageUrl() : '' }}">
            Load More Replies
        </button>
        <div class="spinner-border spinner-border-sm text-primary d-none js-load-more-spinner" role="status"></div>
    </div>
</div>

@once
<script>
document.addEventListener('click', function(e) {
    if (!e.target.classList.contains('js-load-more-btn')) return;

    const btn = e.target;
    const url = btn.getAttribute('data-next-url');
    if (!url) return;

    const wrapper = btn.closest('.js-load-more-wrapper');
    const spinner = wrapper.querySelector('.js-load-more-spinner');
    const container = btn.closest('.js-replies-wrapper');
    const contentArea = container.querySelector('.js-replies-content');

    btn.classList.add('d-none');
    spinner.classList.remove('d-none');

    fetch(url, {
        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(res => res.json())
    .then(data => {
        // Append newly loaded items cleanly to the list
        contentArea.insertAdjacentHTML('beforeend', data.html);

        if (!data.next_page_url) {
            wrapper.remove();
        } else {
            btn.setAttribute('data-next-url', data.next_page_url);
            btn.classList.remove('d-none');
        }
        spinner.classList.add('d-none');
    })
    .catch(err => {
        console.error('Error paginating replies:', err);
        btn.classList.remove('d-none');
        spinner.classList.add('d-none');
    });
});
</script>
@endonce