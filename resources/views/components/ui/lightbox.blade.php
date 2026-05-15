<style>
    #global-lightbox { position: fixed; top: 0; left: 0; width: 100vw; height: 100vh; background: rgba(0,0,0,0.95); z-index: 9999; display: flex; align-items: center; justify-content: center; opacity: 0; visibility: hidden; transition: opacity 0.3s ease, visibility 0.3s ease; }
    #global-lightbox.active { opacity: 1; visibility: visible; }
    .lightbox-btn { position: absolute; background: rgba(255,255,255,0.1); color: white; border: none; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: background 0.2s; z-index: 10000; }
    .lightbox-btn:hover { background: rgba(255,255,255,0.3); }
    .lightbox-close { top: 20px; right: 20px; font-size: 24px; }
    .lightbox-prev { left: 20px; font-size: 20px; }
    .lightbox-next { right: 20px; font-size: 20px; }
    .lightbox-content-wrapper { max-width: 90vw; max-height: 90vh; display: flex; align-items: center; justify-content: center; position: relative; }
    .lightbox-element { max-width: 100%; max-height: 90vh; object-fit: contain; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
    .lightbox-counter { position: absolute; bottom: 20px; left: 50%; transform: translateX(-50%); color: white; font-size: 14px; background: rgba(0,0,0,0.6); padding: 6px 14px; border-radius: 20px; letter-spacing: 1px; }
</style>

<div id="global-lightbox">
    <button class="lightbox-btn lightbox-close" aria-label="Close">&times;</button>
    <button class="lightbox-btn lightbox-prev" aria-label="Previous">&#10094;</button>
    <div class="lightbox-content-wrapper" id="lightbox-content"></div>
    <button class="lightbox-btn lightbox-next" aria-label="Next">&#10095;</button>
    <div class="lightbox-counter" id="lightbox-counter"></div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const lightbox = document.getElementById('global-lightbox');
        const lbContent = document.getElementById('lightbox-content');
        const lbCounter = document.getElementById('lightbox-counter');
        let currentLbMedia = [];
        let currentLbIndex = 0;

        const renderLightboxMedia = () => {
            if (!currentLbMedia.length) return;
            const item = currentLbMedia[currentLbIndex];
            lbContent.innerHTML = item.type === 'video' ? `<video src="${item.url}" class="lightbox-element" controls autoplay></video>` : `<img src="${item.url}" class="lightbox-element">`;
            lbCounter.textContent = `${currentLbIndex + 1} / ${currentLbMedia.length}`;
            const showControls = currentLbMedia.length > 1;
            document.querySelector('.lightbox-prev').style.display = showControls ? 'flex' : 'none';
            document.querySelector('.lightbox-next').style.display = showControls ? 'flex' : 'none';
        };

        window.openGlobalLightbox = (mediaArray, startIndex) => {
            currentLbMedia = mediaArray; currentLbIndex = parseInt(startIndex, 10);
            renderLightboxMedia(); lightbox.classList.add('active'); document.body.style.overflow = 'hidden'; 
        };

        const closeLightbox = () => { lightbox.classList.remove('active'); document.body.style.overflow = ''; setTimeout(() => lbContent.innerHTML = '', 300); };
        const navMedia = (dir) => { currentLbIndex = (currentLbIndex + dir + currentLbMedia.length) % currentLbMedia.length; renderLightboxMedia(); };

        document.querySelector('.lightbox-close').addEventListener('click', closeLightbox);
        document.querySelector('.lightbox-next').addEventListener('click', () => navMedia(1));
        document.querySelector('.lightbox-prev').addEventListener('click', () => navMedia(-1));
        lightbox.addEventListener('click', (e) => { if (e.target === lightbox || e.target === lbContent) closeLightbox(); });
        document.addEventListener('keydown', (e) => {
            if (!lightbox.classList.contains('active')) return;
            if (e.key === 'Escape') closeLightbox();
            if (e.key === 'ArrowRight') navMedia(1);
            if (e.key === 'ArrowLeft') navMedia(-1);
        });
    });
</script>