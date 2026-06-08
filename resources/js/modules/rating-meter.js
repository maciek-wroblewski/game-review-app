export default class RatingMeter {
    constructor() {
        this.activeContainer = null;
        this.activeOverlay = null;
        this.activeFill = null;
        this.activeText = null;
        this.isDragging = false;

        document.addEventListener('mousedown', this.startDrag.bind(this));
        document.addEventListener('mousemove', this.handleMouseMove.bind(this));
        document.addEventListener('mouseup', this.stopDrag.bind(this));

        document.addEventListener('touchstart', this.startDrag.bind(this), { passive: false });
        document.addEventListener('touchmove', this.handleTouchMove.bind(this), { passive: false });
        document.addEventListener('touchend', this.stopDrag.bind(this));

        // Clear button reset
        document.addEventListener('click', this.handleClear.bind(this));

        // Edit Mode Sync
        document.addEventListener('toggle-edit', this.handleToggleEdit.bind(this));
    }

    setVisualRating(container, fill, text, newRating) {
        if (fill && text) {
            fill.style.height = `${newRating * 10}%`;
            text.innerText = `${newRating} / 10`;
            
            fill.classList.remove('bg-success', 'bg-warning', 'bg-danger');
            if (newRating >= 7) fill.classList.add('bg-success');
            else if (newRating >= 4) fill.classList.add('bg-warning');
            else fill.classList.add('bg-danger');
        }
        container.dataset.currentRating = newRating;
    }

    updateRating(e) {
        if (!this.activeOverlay) return;
        const clientY = e.touches ? e.touches[0].clientY : e.clientY;
        const rect = this.activeOverlay.getBoundingClientRect();
        const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
        const newRating = Math.round(percent * 10);
        this.setVisualRating(this.activeContainer, this.activeFill, this.activeText, newRating);
    }

    startDrag(e) {
        const overlay = e.target.closest('.js-meter-overlay');
        if (!overlay) return;
        if (window.getComputedStyle(overlay).display === 'none') return;

        this.activeOverlay = overlay;
        this.activeContainer = overlay.closest('.rating-meter-container');
        if (!this.activeContainer) return;
        this.activeFill = this.activeContainer.querySelector('.js-meter-fill');
        this.activeText = this.activeContainer.querySelector('.js-meter-text');

        this.isDragging = true;
        this.updateRating(e);
        if (e.cancelable) e.preventDefault();
    }

    handleMouseMove(e) {
        if (this.isDragging) this.updateRating(e);
    }

    handleTouchMove(e) {
        if (this.isDragging) this.updateRating(e);
    }

    stopDrag() {
        this.isDragging = false;
        this.activeOverlay = null;
        this.activeContainer = null;
        this.activeFill = null;
        this.activeText = null;
    }

    handleClear(e) {
        if (e.target.closest('.js-btn-create-clear')) {
            const createCard = e.target.closest('.js-create-post-card');
            if (createCard) {
                const container = createCard.querySelector('.rating-meter-container');
                if (container) {
                    const fill = container.querySelector('.js-meter-fill');
                    const text = container.querySelector('.js-meter-text');
                    this.setVisualRating(container, fill, text, 10);
                }
            }
        }
    }

    handleToggleEdit(e) {
        setTimeout(() => {
            const editContainer = e.target;
            const card = e.detail?.card || editContainer.closest('.js-post-card');
            if (!card) return;
            const container = card.querySelector('.rating-meter-container');
            if (!container) return;
            const overlay = container.querySelector('.js-meter-overlay');
            if (!overlay) return;
            const fill = container.querySelector('.js-meter-fill');
            const text = container.querySelector('.js-meter-text');

            const isEditing = editContainer.dataset.open === 'true';

            if (isEditing) {
                overlay.style.display = 'block'; 
                card.classList.add('is-editing');
            } else {
                overlay.style.display = 'none'; 
                card.classList.remove('is-editing');
                const orig = parseInt(container.dataset.originalRating, 10);
                this.setVisualRating(container, fill, text, orig);
            }
        }, 0);
    }
}

