export default class PostMenu {
    constructor() {
        document.addEventListener('click', async (e) => {
            // We must keep this custom event trigger so the rest of your post card layout 
            // knows when to show/hide the js-edit-container form template.
            const editBtn = e.target.closest('.js-btn-edit');
            if (editBtn) {
                const card = editBtn.closest('.js-post-card');
                if (!card) return;

                const editContainer = card.querySelector('.js-edit-container');
                if (!editContainer) return;

                editContainer.dispatchEvent(new CustomEvent('toggle-edit', { 
                    bubbles: true,
                    detail: { card } 
                }));
            }
        });
    }
}
