export default class TruncateText {
    constructor() {
        const wrappers = document.querySelectorAll('[data-js="truncate-text"]');

        wrappers.forEach(wrapper => {
            const content = wrapper.querySelector('.truncate-content');
            const btn = wrapper.querySelector('.truncate-btn');

            if (!content || !btn) return;

            if (content.scrollHeight > content.clientHeight) {
                btn.classList.remove('d-none');
                wrapper.dataset.collapsedHeight = content.clientHeight;
            }

            btn.addEventListener('click', () => {
                const isExpanded = wrapper.classList.contains('is-expanded');
                const collapsedHeight = parseInt(wrapper.dataset.collapsedHeight, 10);

                const readMoreText = btn.dataset.readMoreText || 'Read more';
                const readLessText = btn.dataset.readLessText || 'Read less';

                if (!isExpanded) {
                    const fullHeight = content.scrollHeight;

                    content.style.maxHeight = collapsedHeight + 'px';
                    wrapper.classList.add('is-expanded');

                    content.offsetHeight;

                    content.style.maxHeight = fullHeight + 'px';
                    btn.textContent = readLessText;

                    const onTransitionEnd = (e) => {
                        if (e.propertyName === 'max-height') {
                            content.style.maxHeight = 'none';
                            content.removeEventListener('transitionend', onTransitionEnd);
                        }
                    };
                    content.addEventListener('transitionend', onTransitionEnd);

                } else {
                    const fullHeight = content.scrollHeight;

                    content.style.maxHeight = fullHeight + 'px';
                    content.offsetHeight;

                    content.style.maxHeight = collapsedHeight + 'px';
                    btn.textContent = readMoreText;

                    const onTransitionEnd = (e) => {
                        if (e.propertyName === 'max-height') {
                            wrapper.classList.remove('is-expanded');
                            content.style.maxHeight = '';
                            content.removeEventListener('transitionend', onTransitionEnd);
                        }
                    };
                    content.addEventListener('transitionend', onTransitionEnd);
                }
            });
        });
    }
}
