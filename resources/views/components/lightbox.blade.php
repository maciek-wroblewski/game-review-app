<div id="globalLightbox" class="lightbox-overlay d-none">
    <div class="lightbox-backdrop" onclick="window.Lightbox.close()"></div>
    
    <div class="lightbox-header">
        <div id="lightboxCounter" class="lightbox-counter fw-bold text-white shadow-sm px-3 py-1 rounded-pill" style="background: rgba(255,255,255,0.2); backdrop-filter: blur(10px);">
            1 / 1
        </div>
        <button class="btn btn-link text-white p-0 text-decoration-none" onclick="window.Lightbox.close()">
            <i class="bi bi-x-lg fs-4"></i>
        </button>
    </div>

    <button id="lightboxPrev" class="lightbox-nav lightbox-prev" onclick="window.Lightbox.prev()">
        <i class="bi bi-chevron-left fs-3"></i>
    </button>

    <div class="lightbox-content-container" id="lightboxContentContainer" onclick="window.Lightbox.close()">
        </div>

    <button id="lightboxNext" class="lightbox-nav lightbox-next" onclick="window.Lightbox.next()">
        <i class="bi bi-chevron-right fs-3"></i>
    </button>
</div>

@once
<style>
    .lightbox-overlay {
        position: fixed; top: 0; left: 0; width: 100vw; height: 100vh;
        z-index: 10500; display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity 0.25s ease-in-out;
    }
    .lightbox-overlay.show { opacity: 1; }
    
    .lightbox-backdrop {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(0,0,0,0.92);
    }
    
    .lightbox-header {
        position: absolute; top: 20px; right: 20px; left: 20px; z-index: 10502;
        display: flex; justify-content: space-between; align-items: center;
    }

    .lightbox-content-container {
        position: relative; z-index: 10501; width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
        padding: 60px 80px; /* Leave room for arrows and header */
    }

    .lightbox-media {
        max-width: 100%; max-height: 100%; object-fit: contain;
        border-radius: 8px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);
    }
    
    .lightbox-nav {
        position: absolute; z-index: 10502; top: 50%; transform: translateY(-50%);
        background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); 
        color: white; width: 50px; height: 50px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center; cursor: pointer;
        transition: all 0.2s ease; backdrop-filter: blur(5px);
    }
    .lightbox-nav:hover { background: rgba(255,255,255,0.3); transform: translateY(-50%) scale(1.1); }
    .lightbox-nav.disabled { opacity: 0; pointer-events: none; }
    
    .lightbox-prev { left: 20px; }
    .lightbox-next { right: 20px; }

    /* --- ANIMATION CLASSES --- */
    .anim-slide-next { animation: slideNext 0.3s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    .anim-slide-prev { animation: slidePrev 0.3s cubic-bezier(0.25, 1, 0.5, 1) forwards; }
    .anim-pop-in { animation: popIn 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards; }

    @keyframes slideNext {
        from { transform: translateX(80px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes slidePrev {
        from { transform: translateX(-80px); opacity: 0; }
        to { transform: translateX(0); opacity: 1; }
    }
    @keyframes popIn {
        from { transform: translateY(20px) scale(0.95); opacity: 0; }
        to { transform: translateY(0) scale(1); opacity: 1; }
    }

    /* Mobile adjustments */
    @media (max-width: 768px) {
        .lightbox-content-container { padding: 60px 0; }
        .lightbox-nav { width: 40px; height: 40px; }
        .lightbox-prev { left: 10px; }
        .lightbox-next { right: 10px; }
    }
</style>

<script>
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
                
                // Pass 'init' to trigger the pop-in animation
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
                    // Pass 'prev' to trigger slide-from-left
                    this.render('prev');
                }
            },

            next: function(e) {
                if(e) e.stopPropagation();
                if (currentIndex < mediaItems.length - 1) {
                    currentIndex++;
                    // Pass 'next' to trigger slide-from-right
                    this.render('next');
                }
            },

            render: function(direction = 'next') {
                const media = mediaItems[currentIndex];
                container.innerHTML = '';

                // Determine which animation class to apply based on navigation direction
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

                // Arrow visibility
                btnPrev.classList.toggle('disabled', currentIndex === 0);
                btnNext.classList.toggle('disabled', currentIndex === mediaItems.length - 1);
            }
        };

        // Event Delegation
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

        // Keyboard support
        document.addEventListener('keydown', (e) => {
            if (!overlay.classList.contains('show')) return;
            
            if (e.key === 'Escape') window.Lightbox.close();
            if (e.key === 'ArrowLeft') window.Lightbox.prev();
            if (e.key === 'ArrowRight') window.Lightbox.next();
        });
    });
</script>
@endonce