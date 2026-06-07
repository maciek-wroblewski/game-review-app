export default class PostMenu {
    constructor() {
        document.addEventListener('click', this.handleClick.bind(this));
    }

    handleClick(e) {
        const editBtn = e.target.closest('.js-btn-edit');
        if (!editBtn) return;

        const card = editBtn.closest('.js-post-card');
        if (!card) return;

        const editContainer = card.querySelector('.js-edit-container');
        if (!editContainer) return;

        editContainer.dispatchEvent(new CustomEvent('toggle-edit', { 
            bubbles: true,
            detail: { card } 
        }));
    }
}

