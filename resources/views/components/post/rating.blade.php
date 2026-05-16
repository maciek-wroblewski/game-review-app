@props(['rating'])
@php
    $meterColor = match(true) {
        $rating >= 7 => 'bg-success',
        $rating >= 4 => 'bg-warning',
        default => 'bg-danger',
    };
@endphp

<div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end" style="width: 50px; min-width: 50px; background-color: rgba(0, 0, 0, 0.05); z-index: 1;">
    <div class="js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition {{ $meterColor }}" style="height: {{ $rating * 10 }}%; transition: height 0.3s, background-color 0.3s;">
        <span class="js-meter-text" style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 0.85rem;">{{ $rating }} / 10</span>
    </div>
    <div class="js-rating-overlay position-absolute top-0 start-0 w-100 h-100 d-none cursor-crosshair" style="z-index: 10;" title="Click to set new rating"></div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.js-post-card').forEach(card => {
            const isReview = card.dataset.isReview === 'true';
            const ratingOverlay = card.querySelector('.js-rating-overlay');
            if (!isReview || !ratingOverlay) return;

            let isDragging = false;

            const updateRating = (e) => {
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                const rect = ratingOverlay.getBoundingClientRect();
                const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
                
                const newRating = Math.round(percent * 10);
                const fill = card.querySelector('.js-meter-fill');
                const text = card.querySelector('.js-meter-text');
                
                if (fill && text) {
                    fill.style.height = `${newRating * 10}%`;
                    text.innerText = `${newRating} / 10`;
                    fill.className = `js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition ${newRating >= 7 ? 'bg-success' : (newRating >= 4 ? 'bg-warning' : 'bg-danger')}`;
                }
            };

            const toggleDrag = state => e => { 
                isDragging = state; 
                if (state) updateRating(e); 
                if (e.cancelable) e.preventDefault(); 
            };

            const onDrag = e => { 
                if (isDragging) { 
                    updateRating(e); 
                    if (e.cancelable) e.preventDefault(); 
                } 
            };

            ratingOverlay.addEventListener('mousedown', toggleDrag(true));
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', toggleDrag(false));

            ratingOverlay.addEventListener('touchstart', toggleDrag(true), { passive: false });
            document.addEventListener('touchmove', onDrag, { passive: false });
            document.addEventListener('touchend', toggleDrag(false));
        });
    });
</script>
@endonce