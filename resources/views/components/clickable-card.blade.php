@props(['link' => '#'])

<div class="nested-card" data-card-link="{{ $link }}">
    {{ $slot }}
</div>

@once
<style>
.nested-card {
    position: relative;
    cursor: pointer;
    display: block;
    transition: transform 0.2s ease;
}

/* Ensure inner interactive elements override the pointer and stay clickable */
.nested-card a, 
.nested-card button, 
.nested-card input,
.nested-card select,
.nested-card textarea,
.nested-card .dropdown-menu {
    position: relative;
    z-index: 1;
    cursor: auto;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const initNestedCards = () => {
        document.querySelectorAll('[data-card-link]:not([data-card-initialized])').forEach(card => {
            card.setAttribute('data-card-initialized', 'true');

            card.addEventListener('click', (e) => {
                // 1. Elements that should bypass the background link entirely
                const bypassSelector = 'a, button, input, select, textarea, label, .dropdown-menu, [data-card-bypass]';
                
                if (e.target.closest(bypassSelector)) {
                    // DO NOT stop propagation here. Let it bubble up to document listeners!
                    return;
                }

                // 2. Only stop propagation if it's a real background click intended for THIS card.
                // This stops parent nested cards from triggering.
                e.stopPropagation();

                const url = card.getAttribute('data-card-link');
                if (!url || url === '#') return;

                if (e.ctrlKey || e.metaKey || e.button === 1) {
                    window.open(url, '_blank');
                } else {
                    window.location.href = url;
                }
            });
        });
    };

    initNestedCards();
    document.addEventListener('livewire:navigated', initNestedCards);
});
</script>
@endonce