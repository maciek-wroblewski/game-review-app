@props(['rating' => 0, 'editable' => false, 'class' => '', 'name' => 'rating'])

@php
    $meterColor = match(true) { 
        $rating >= 7 => 'bg-success', 
        $rating >= 4 => 'bg-warning', 
        default => 'bg-danger' 
    };
@endphp

<style>
    .js-meter-overlay {
        cursor: ns-resize;
    }

    .is-editing .js-meter-overlay {
        display: block !important;
    }
</style>

<div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end {{ $class }}" 
     style="width: 50px; min-width: 50px; background-color: rgba(0,0,0,0.05); z-index: 1;"
     data-original-rating="{{ $rating }}"
     data-current-rating="{{ $rating }}">
    
    <div class="js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition {{ $meterColor }}" 
         style="height: {{ $rating * 10 }}%; transition: height 0.3s, background-color 0.3s;">
        <span class="js-meter-text" style="font-size: 0.85rem;">{{ $rating }} / 10</span>
    </div>

    <div class="js-meter-overlay position-absolute top-0 start-0 w-100 h-100" 
         style="z-index: 10; {{ $editable ? '' : 'display: none;' }}" 
         title="Click and drag to set rating"></div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.rating-meter-container').forEach(container => {
        const overlay = container.querySelector('.js-meter-overlay');
        const fill = container.querySelector('.js-meter-fill');
        const text = container.querySelector('.js-meter-text');
        
        const editCard = container.closest('.js-post-card');
        const createCard = container.closest('.js-create-post-card');
        
        if (!overlay) return;

        let isDragging = false;

        // --- Core Logic: Visual & Data Sync ---
        const setVisualRating = (newRating) => {
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
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const rect = overlay.getBoundingClientRect();
            const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
            const newRating = Math.round(percent * 10);
            setVisualRating(newRating);
        };

        const toggleDrag = (state) => (e) => { 
            if (window.getComputedStyle(overlay).display === 'none') return;
            isDragging = state; 
            if (state) updateRating(e); 
            if (e.cancelable) e.preventDefault(); 
        };

        overlay.addEventListener('mousedown', toggleDrag(true));
        document.addEventListener('mousemove', (e) => { if(isDragging) updateRating(e); });
        document.addEventListener('mouseup', toggleDrag(false));

        overlay.addEventListener('touchstart', toggleDrag(true), { passive: false });
        document.addEventListener('touchmove', (e) => { if(isDragging) updateRating(e); }, { passive: false });
        document.addEventListener('touchend', toggleDrag(false));

        if (createCard) {
            overlay.style.display = 'block';
            createCard.addEventListener('click', (e) => {
                if (e.target.closest('.js-btn-create-clear')) {
                    setVisualRating(10);
                }
            });
        } 
        
        else if (editCard) {
            editCard.addEventListener('toggle-edit', (e) => {
                
                setTimeout(() => {
                    const editContainer = e.target;
                    const isEditing = editContainer.dataset.open === 'true';

                    if (isEditing) {
                        // Activate drag overlay and magical CSS
                        overlay.style.display = 'block'; 
                        editCard.classList.add('is-editing');
                    } else {
                        // Deactivate and reset
                        overlay.style.display = 'none'; 
                        editCard.classList.remove('is-editing');
                        
                        // Because the event broadcasted "false", it means edit mode was cancelled or closed
                        const orig = parseInt(container.dataset.originalRating, 10);
                        setVisualRating(orig);
                    }
                }, 0);
                
            });
        }
    });
});
</script>
@endonce