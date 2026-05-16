@props(['text', 'maxLines' => 3])

<div class="js-text-wrapper">
    <p class="js-text-body card-text mb-2" style="white-space: pre-line; font-size: 0.9rem;">{{ $text }}</p>
</div>
<button class="js-btn-read-more btn btn-link text-decoration-none p-0 mb-2 fw-bold small" data-max-lines="{{ $maxLines }}" style="display: none;">Read more...</button>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-text-wrapper').forEach(wrapper => {
        const btn = wrapper.parentElement.querySelector('.js-btn-read-more');
        const p = wrapper.querySelector('.js-text-body');
        if (!p || !btn) return;
        requestAnimationFrame(() => {
            const lineHeight = parseFloat(window.getComputedStyle(p).lineHeight) || 20;
            if (Math.round(p.scrollHeight / lineHeight) > parseInt(btn.dataset.maxLines)) {
                wrapper.classList.add('text-truncate-container');
                wrapper.style.setProperty('--line-clamp', btn.dataset.maxLines);
                btn.style.display = 'inline-block';
            }
        });
    });
    document.addEventListener('click', (e) => {
        if (e.target.closest('.js-btn-read-more')) {
            const btn = e.target.closest('.js-btn-read-more');
            const wrapper = btn.previousElementSibling;
            const startH = wrapper.offsetHeight;
            wrapper.classList.remove('text-truncate-container');
            btn.style.display = 'none';
            const endH = wrapper.offsetHeight;
            const anim = wrapper.animate([{ height: `${startH}px`, overflow: 'hidden' }, { height: `${endH}px`, overflow: 'hidden' }], { duration: 250, easing: 'ease-out' });
            anim.onfinish = () => { wrapper.style.height = ''; wrapper.style.overflow = ''; };
        }
    });
});
</script>
@endonce
