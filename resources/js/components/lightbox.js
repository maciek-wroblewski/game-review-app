export default function initLightbox() {
    document.addEventListener('DOMContentLoaded', () => {
        const overlay = document.getElementById('globalLightbox');
        const container = document.getElementById('lightboxContentContainer');
        const counter = document.getElementById('lightboxCounter');
        const btnPrev = document.getElementById('lightboxPrev');
        const btnNext = document.getElementById('lightboxNext');

        let mediaItems = [];
        let currentIndex = 0;

        window.Lightbox = {
            open: function(items, startIndex = 0) {
                if (!items || items.length === 0) return;
                
                mediaItems = items;
                currentIndex = parseInt(startIndex);
                
                document.body.style.overflow = 'hidden'; 
                overlay.classList.remove('d-none');
                
                setTimeout(() => overlay.classList.add('show'), 10); 
                
                this.render('init');
            },

            close: function() {
                overlay.classList.remove('show');
                document.body.style.overflow = ''; 
                
                setTimeout(() => {
                    overlay.classList.add('d-none');
                    container.innerHTML = ''; 
                }, 250);
            },

            prev: function(e) {
                if(e) e.stopPropagation();
                if (currentIndex > 0) {
                    currentIndex--;
                    this.render('prev');
                }
            },

            next: function(e) {
                if(e) e.stopPropagation();
                if (currentIndex < mediaItems.length - 1) {
                    currentIndex++;
                    this.render('next');
                }
            },

            render: function(direction = 'next') {
                const media = mediaItems[currentIndex];
                container.innerHTML = '';

                let animClass = 'anim-pop-in';
                if (direction === 'next') animClass = 'anim-slide-next';
                if (direction === 'prev') animClass = 'anim-slide-prev';

                let elementHtml = '';
                if (media.type === 'video') {
                    elementHtml = `<video src="${media.url}" class="lightbox-media ${animClass}" autoplay controls onclick="event.stopPropagation()"></video>`;
                } else {
                    elementHtml = `<img src="${media.url}" class="lightbox-media ${animClass}" alt="Media" onclick="event.stopPropagation()">`;
                }
                
                container.innerHTML = elementHtml;
                counter.innerText = `${currentIndex + 1} / ${mediaItems.length}`;

                btnPrev.classList.toggle('disabled', currentIndex === 0);
                btnNext.classList.toggle('disabled', currentIndex === mediaItems.length - 1);
            }
        };

        document.body.addEventListener('click', (e) => {
            const trigger = e.target.closest('.js-lightbox-trigger');
            if (!trigger) return;

            e.preventDefault();
            
            const mediaContainer = trigger.closest('.js-media-container');
            if (mediaContainer && mediaContainer.dataset.media) {
                const mediaData = JSON.parse(mediaContainer.dataset.media);
                const startIndex = trigger.dataset.index || 0;
                window.Lightbox.open(mediaData, startIndex);
            }
        });

        document.addEventListener('keydown', (e) => {
            if (!overlay.classList.contains('show')) return;
            
            if (e.key === 'Escape') window.Lightbox.close();
            if (e.key === 'ArrowLeft') window.Lightbox.prev();
            if (e.key === 'ArrowRight') window.Lightbox.next();
        });
    });
}
