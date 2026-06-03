export default class ClickableCard {
    constructor() {
        // We use dynamic event delegation so cards added via AJAX/Livewire work instantly!
        document.addEventListener('click', (e) => {
            // Find if the click occurred on or inside a nested-card
            const card = e.target.closest('[data-card-link]');
            if (!card) return;

            // Elements that should bypass the background link click action entirely
            const bypassSelector = 'a, button, input, select, textarea, label, .dropdown-menu, [data-card-bypass]';
            const closestBypass = e.target.closest(bypassSelector);
            
            if (closestBypass && card.contains(closestBypass)) {
                return;
            }

            // Only stop propagation if it's a real background click intended for THIS card.
            e.stopPropagation();

            const url = card.getAttribute('data-card-link');
            if (!url || url === '#') return;

            if (e.ctrlKey || e.metaKey || e.button === 1) {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        });
    }
}
