export default class RepliesToggle {
    constructor() {
        document.addEventListener('click', this.handleClick.bind(this));
    }

    handleClick(e) {
        const btn = e.target.closest('.js-btn-show-replies');
        if (!btn) return;

        // 1. Go UP to find the shared parent card/wrapper
        const postWrapper = btn.closest('.js-post-wrapper');
        if (!postWrapper) return;

        // 2. Go DOWN to find the specific comment list inside this wrapper
        const container = postWrapper.querySelector('.js-comment-list-container');
        if (!container) return;

        e.preventDefault();
        e.stopPropagation();

        // 3. Dispatch your custom event!
        container.dispatchEvent(new CustomEvent('toggle-replies', { 
            bubbles: true, 
            detail: { btn } 
        }));
    }
}

