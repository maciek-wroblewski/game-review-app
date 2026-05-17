@props(['href' => null, 'class' => ''])
<div {{ $attributes->merge(['class' => "clickable-card {$class}", 'data-href' => $href]) }}>
    {{ $slot }}
</div>

@once
<script>
document.addEventListener('click', (e) => {
    const card = e.target.closest('.clickable-card');
    if (!card) return;
    // Ignore clicks on interactive elements
    if (e.target.closest('a, button, .dropdown-menu, input, textarea, select, .cursor-crosshair')) return;
    const href = card.dataset.href;
    if (href) window.location.href = href;
});
</script>
@endonce
