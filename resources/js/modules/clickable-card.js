export default class ClickableCard {
    constructor() {
        document.addEventListener('click', this.handleClick.bind(this));
    }

    handleClick(e) {
        const card = e.target.closest('[data-card-link]');
        if (!card) return;

        const bypassSelector = 'a, button, input, select, textarea, label, .dropdown-menu, [data-card-bypass]';
        const closestBypass = e.target.closest(bypassSelector);
        
        if (closestBypass && card.contains(closestBypass)) {
            return;
        }

        e.stopPropagation();

        const url = card.getAttribute('data-card-link');
        if (!url || url === '#') return;

        if (e.ctrlKey || e.metaKey || e.button === 1) {
            window.open(url, '_blank');
        } else {
            window.location.href = url;
        }
    }
}

