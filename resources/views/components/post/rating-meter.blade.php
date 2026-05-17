@props(['rating' => 0, 'editable' => false, 'class' => '', 'name' => 'rating'])

@php
    $meterColor = match(true) { 
        $rating >= 7 => 'bg-success', 
        $rating >= 4 => 'bg-warning', 
        default => 'bg-danger' 
    };
@endphp

<style>
    /* Magically show the drag overlay when the parent post enters edit mode */
    .is-editing .js-meter-overlay {
        display: block !important;
    }
    /* Optional: Add a subtle visual cue that the meter is now draggable */
    .is-editing .js-meter-fill {
        /* box-shadow: inset 0 0 0 2px rgba(255,255,255,0.4); */
    }
</style>

<div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end {{ $class }}" 
     style="width: 50px; min-width: 50px; background-color: rgba(0,0,0,0.05); z-index: 1;"
     data-original-rating="{{ $rating }}"
     data-current-rating="{{ $rating }}">
    
    <div class="js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition {{ $meterColor }}" 
         style="height: {{ $rating * 10 }}%; transition: height 0.3s, background-color 0.3s;">
        <span class="js-meter-text" style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 0.85rem;">{{ $rating }} / 10</span>
    </div>

    {{-- Overlay is always present, but hidden unless passed 'editable' OR parent has .is-editing class --}}
    <div class="js-meter-overlay position-absolute top-0 start-0 w-100 h-100 cursor-crosshair" 
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
        
        if (!overlay) return;

        let isDragging = false;

        // Extracted visual update logic so it can be used for dragging AND resetting
        const setVisualRating = (newRating) => {
            if (fill && text) {
                fill.style.height = `${newRating * 10}%`;
                text.innerText = `${newRating} / 10`;
                fill.className = fill.className.replace(/bg-(success|warning|danger)/g, '') + 
                                 ` ${newRating >= 7 ? 'bg-success' : (newRating >= 4 ? 'bg-warning' : 'bg-danger')}`;
            }
        };

        const updateRating = (e) => {
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            const rect = overlay.getBoundingClientRect();
            const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
            const newRating = Math.round(percent * 10);

            setVisualRating(newRating);
            container.dataset.currentRating = newRating; // Update for parent form to read
        };

        const toggleDrag = (state) => (e) => { 
            // Crucial: Prevent dragging if the overlay isn't visually active (i.e., not in edit mode)
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

        // Decoupled Listener: Reset the rating visually if the edit is canceled
        const parentCard = container.closest('.js-post-card');
        if (parentCard) {
            parentCard.addEventListener('post:cancel-edit', () => {
                const orig = parseInt(container.dataset.originalRating, 10);
                container.dataset.currentRating = orig;
                setVisualRating(orig);
            });
        }
    });
});
</script>
@endonce