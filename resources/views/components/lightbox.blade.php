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