export default class ReplyToggle {
    constructor() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.js-btn-reply');
            if (!btn) return;

            const postWrapper = btn.closest('.js-post-wrapper');
            if (!postWrapper) return;

            // 2. Go DOWN to find the specific container inside THIS wrapper
            const container = postWrapper.querySelector('.js-reply-container');
            if (!container) return;

            e.preventDefault();
            e.stopPropagation();

            const isOpen = container.dataset.open === 'true';

            if (!isOpen) {
                container.style.maxHeight = container.scrollHeight + 'px';
                container.style.opacity = '1';
                container.dataset.open = 'true';
            } else {
                container.style.maxHeight = '0';
                container.style.opacity = '0';
                container.dataset.open = 'false';
            }
        });
    }
}
