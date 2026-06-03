export default class RatingMeter {
    constructor() {
        let activeContainer = null;
        let activeOverlay = null;
        let activeFill = null;
        let activeText = null;
        let isDragging = false;

        const setVisualRating = (container, fill, text, newRating) => {
            if (fill && text) {
                fill.style.height = `${newRating * 10}%`;
                text.innerText = `${newRating} / 10`;
                
                fill.classList.remove('bg-success', 'bg-warning', 'bg-danger');
                if (newRating >= 7) fill.classList.add('bg-success');
                else if (newRating >= 4) fill.classList.add('bg-warning');
                else fill.classList.add('bg-danger');
            }
            container.dataset.currentRating = newRating;
        };

        const updateRating = (e) => {
            if (!activeOverlay) return;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const rect = activeOverlay.getBoundingClientRect();
            const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
            const newRating = Math.round(percent * 10);
            setVisualRating(activeContainer, activeFill, activeText, newRating);
        };

        const startDrag = (e) => {
            const overlay = e.target.closest('.js-meter-overlay');
            if (!overlay) return;
            if (window.getComputedStyle(overlay).display === 'none') return;

            activeOverlay = overlay;
            activeContainer = overlay.closest('.rating-meter-container');
            if (!activeContainer) return;
            activeFill = activeContainer.querySelector('.js-meter-fill');
            activeText = activeContainer.querySelector('.js-meter-text');

            isDragging = true;
            updateRating(e);
            if (e.cancelable) e.preventDefault();
        };

        const stopDrag = () => {
            isDragging = false;
            activeOverlay = null;
            activeContainer = null;
            activeFill = null;
            activeText = null;
        };

        document.addEventListener('mousedown', startDrag);
        document.addEventListener('mousemove', (e) => { if (isDragging) updateRating(e); });
        document.addEventListener('mouseup', stopDrag);

        document.addEventListener('touchstart', startDrag, { passive: false });
        document.addEventListener('touchmove', (e) => { if (isDragging) updateRating(e); }, { passive: false });
        document.addEventListener('touchend', stopDrag);

        // Clear button reset
        document.addEventListener('click', (e) => {
            if (e.target.closest('.js-btn-create-clear')) {
                const createCard = e.target.closest('.js-create-post-card');
                if (createCard) {
                    const container = createCard.querySelector('.rating-meter-container');
                    if (container) {
                        const fill = container.querySelector('.js-meter-fill');
                        const text = container.querySelector('.js-meter-text');
                        setVisualRating(container, fill, text, 10);
                    }
                }
            }
        });

        // Edit Mode Sync
        document.addEventListener('toggle-edit', (e) => {
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
                    setVisualRating(container, fill, text, orig);
                }
            }, 0);
        });
    }
}
