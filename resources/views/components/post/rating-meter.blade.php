@props(['rating' => 0, 'editable' => false])
@php $color = match(true) { $rating >= 7 => 'bg-success', $rating >= 4 => 'bg-warning', default => 'bg-danger'; } @endphp

<div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end" style="width: 40px; min-width: 40px; background-color: rgba(0,0,0,0.05);">
    <div class="js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold {{ $color }}" style="height: {{ $rating * 10 }}%; transition: height 0.3s, background-color 0.3s;">
        <span class="js-meter-text" style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 0.75rem;">{{ $rating }} / 10</span>
    </div>
    @if($editable)
        <div class="js-rating-overlay position-absolute top-0 start-0 w-100 h-100 cursor-crosshair" style="z-index: 10;" title="Drag to change rating"></div>
    @endif
</div>

@once
@if($editable)
<script>
document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.js-rating-overlay').forEach(overlay => {
        let dragging = false;
        const update = (e) => {
            const y = e.touches ? e.touches[0].clientY : e.clientY;
            const rect = overlay.getBoundingClientRect();
            const pct = Math.max(0.1, Math.min(1.0, 1 - ((y - rect.top) / rect.height)));
            const val = Math.round(pct * 10);
            const fill = overlay.closest('.rating-meter-container').querySelector('.js-meter-fill');
            const text = overlay.closest('.rating-meter-container').querySelector('.js-meter-text');
            fill.style.height = `${val * 10}%`;
            text.innerText = `${val} / 10`;
            fill.className = `js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold ${val >= 7 ? 'bg-success' : (val >= 4 ? 'bg-warning' : 'bg-danger')}`;
        };
        const toggle = (state) => (e) => { dragging = state; if(state) update(e); if(e.cancelable) e.preventDefault(); };
        overlay.addEventListener('mousedown', toggle(true));
        document.addEventListener('mousemove', (e) => dragging && update(e));
        document.addEventListener('mouseup', toggle(false));
        overlay.addEventListener('touchstart', toggle(true), {passive:false});
        document.addEventListener('touchmove', (e) => dragging && update(e), {passive:false});
        document.addEventListener('touchend', toggle(false));
    });
});
</script>
@endif
@endonce
