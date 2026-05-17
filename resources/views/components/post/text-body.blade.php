@props(['body'])
<div class="js-text-wrapper">
    <p class="js-text-body card-text fs-5 mb-3" style="white-space: pre-line;">{{ $body }}</p>
</div>
<button class="js-btn-read-more btn btn-link text-decoration-none p-0 mb-3 fw-bold" style="display: none;">Read more...</button>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-text-wrapper').forEach(wrapper => {
        const btn = wrapper.closest('.js-view-mode, .js-edit-mode')?.querySelector('.js-btn-read-more');
        if (!btn) return;
        const p = wrapper.querySelector('.js-text-body');
        if (!p) return;
        requestAnimationFrame(() => {
            const lineHeight = parseFloat(window.getComputedStyle(p).lineHeight) || 24;
            const lineCount = Math.round(p.scrollHeight / lineHeight);
            const mediaCount = parseInt(wrapper.closest('[data-media-count]')?.dataset.mediaCount || 0);
            let shouldClamp = false, clampLines = 3;
            if (mediaCount > 0 && lineCount > 1) { shouldClamp = true; clampLines = 1; }
            else if (mediaCount === 0 && lineCount > 5) { shouldClamp = true; clampLines = 5; }
            
            if (shouldClamp) {
                wrapper.classList.add('text-truncate-container');
                wrapper.style.setProperty('--line-clamp', clampLines);
                btn.style.display = 'block';
            } else {
                wrapper.classList.remove('text-truncate-container');
                btn.style.display = 'none';
            }
        });
    });
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.js-btn-read-more')) return;
        const btn = e.target.closest('.js-btn-read-more');
        const wrapper = btn.previousElementSibling;
        if (!wrapper) return;
        const startHeight = wrapper.offsetHeight;
        wrapper.classList.remove('text-truncate-container');
        btn.style.display = 'none';
        const endHeight = wrapper.offsetHeight;
        const anim = wrapper.animate([
            { height: `${startHeight}px`, overflow: 'hidden' },
            { height: `${endHeight}px`, overflow: 'hidden' }
        ], { duration: 300, easing: 'ease-in-out' });
        anim.onfinish = () => { wrapper.style.height = ''; wrapper.style.overflow = ''; };
    });
});
</script>
@endonce
