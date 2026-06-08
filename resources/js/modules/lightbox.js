export default class Lightbox {
    constructor() {
        this.overlay = null;
        this.container = null;
        this.counter = null;
        this.btnPrev = null;
        this.btnNext = null;
        this.mediaItems = [];
        this.currentIndex = 0;

        document.addEventListener('DOMContentLoaded', this.init.bind(this));
    }

    init() {
        this.overlay = document.getElementById('globalLightbox');
        this.container = document.getElementById('lightboxContentContainer');
        this.counter = document.getElementById('lightboxCounter');
        this.btnPrev = document.getElementById('lightboxPrev');
        this.btnNext = document.getElementById('lightboxNext');

        if (!this.overlay) return;

        // Expose to window for inline onclick handlers
        window.Lightbox = {
            open: (items, startIndex = 0) => this.open(items, startIndex),
            close: () => this.close(),
            prev: (e) => this.prev(e),
            next: (e) => this.next(e)
        };

        // Event delegation for triggers
        document.body.addEventListener('click', this.handleTriggerClick.bind(this));

        // Keyboard support
        document.addEventListener('keydown', this.handleKeyDown.bind(this));
    }

    handleTriggerClick(e) {
        const trigger = e.target.closest('.js-lightbox-trigger');
        if (!trigger) return;

        e.preventDefault();

        const mediaContainer = trigger.closest('.js-media-container');
        if (mediaContainer && mediaContainer.dataset.media) {
            const mediaData = JSON.parse(mediaContainer.dataset.media);
            const startIndex = trigger.dataset.index || 0;
            this.open(mediaData, startIndex);
        }
    }

    handleKeyDown(e) {
        if (!this.overlay || !this.overlay.classList.contains('show')) return;

        if (e.key === 'Escape') this.close();
        if (e.key === 'ArrowLeft') this.prev();
        if (e.key === 'ArrowRight') this.next();
    }

    open(items, startIndex = 0) {
        if (!items || items.length === 0) return;

        this.mediaItems = items;
        this.currentIndex = parseInt(startIndex);

        document.body.style.overflow = 'hidden';
        this.overlay.classList.remove('d-none');

        setTimeout(() => this.overlay.classList.add('show'), 10);

        this.render('init');
    }

    close() {
        this.overlay.classList.remove('show');
        document.body.style.overflow = '';

        setTimeout(() => {
            this.overlay.classList.add('d-none');
            this.container.innerHTML = '';
        }, 250);
    }

    prev(e) {
        if (e) e.stopPropagation();
        if (this.currentIndex > 0) {
            this.currentIndex--;
            this.render('prev');
        }
    }

    next(e) {
        if (e) e.stopPropagation();
        if (this.currentIndex < this.mediaItems.length - 1) {
            this.currentIndex++;
            this.render('next');
        }
    }

    render(direction = 'next') {
        const media = this.mediaItems[this.currentIndex];
        this.container.innerHTML = '';

        let animClass = 'anim-pop-in';
        if (direction === 'next') animClass = 'anim-slide-next';
        if (direction === 'prev') animClass = 'anim-slide-prev';

        let elementHtml = '';
        if (media.type === 'video') {
            elementHtml = `<video src="${media.url}" class="lightbox-media ${animClass}" autoplay controls onclick="event.stopPropagation()"></video>`;
        } else {
            elementHtml = `<img src="${media.url}" class="lightbox-media ${animClass}" alt="Media" onclick="event.stopPropagation()">`;
        }

        this.container.innerHTML = elementHtml;
        this.counter.innerText = `${this.currentIndex + 1} / ${this.mediaItems.length}`;

        this.btnPrev.classList.toggle('disabled', this.currentIndex === 0);
        this.btnNext.classList.toggle('disabled', this.currentIndex === this.mediaItems.length - 1);
    }
}
