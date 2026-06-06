export default class PostEditForm {
    constructor() {
        document.addEventListener('toggle-edit', this.handleToggleEdit.bind(this));
        document.addEventListener('click', this.handleClick.bind(this));
    }

    handleToggleEdit(e) {
        const editContainer = e.target;
        const card = e.detail.card;
        const viewContainer = card.querySelector('.edit_form_collapsable');
        
        if (!viewContainer) return;

        const isOpen = editContainer.dataset.open === 'true';

        if (!isOpen) {
            // Open Edit Mode (Collapse View, Expand Edit)
            viewContainer.style.maxHeight = viewContainer.scrollHeight + 'px';
            viewContainer.offsetHeight; // Force reflow
            viewContainer.style.maxHeight = '0';
            viewContainer.style.opacity = '0';

            editContainer.style.maxHeight = editContainer.scrollHeight + 'px';
            editContainer.style.opacity = '1';
            editContainer.dataset.open = 'true';
        } else {
            // Close Edit Mode (Expand View, Collapse Edit)
            // 1. Transition the view container to its explicit pixel height
            viewContainer.style.maxHeight = viewContainer.scrollHeight + 'px';
            viewContainer.style.opacity = '1';

            // 2. Collapse the edit container smoothly
            editContainer.style.maxHeight = '0';
            editContainer.style.opacity = '0';
            editContainer.dataset.open = 'false';

            // 3. Wait for the transition to finish before resetting to auto ('')
            // This keeps your layout responsive if the window resizes later.
            viewContainer.addEventListener('transitionend', function handler() {
                // Guard check: ensure the user didn't quickly re-open it during the transition
                if (editContainer.dataset.open === 'false') {
                    viewContainer.style.maxHeight = '';
                }
                viewContainer.removeEventListener('transitionend', handler);
            });
        }
    }

    handleClick(e) {
        const cancelBtn = e.target.closest('.js-btn-cancel');
        if (!cancelBtn) return;

        e.preventDefault();
        const card = cancelBtn.closest('.js-post-card');
        const editContainer = cancelBtn.closest('.js-edit-container');

        if (card && editContainer) {
            editContainer.dispatchEvent(new CustomEvent('toggle-edit', { 
                bubbles: true, 
                detail: { card } 
            }));
        }
    }
}

