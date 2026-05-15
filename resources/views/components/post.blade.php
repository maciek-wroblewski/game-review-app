@props(['post', 'layout' => 'full'])

@php
    $isReview = method_exists($post, 'isReview') && $post->isReview() && $post->review;
@endphp

<style>
    /* 1. Smooth Scaling/Resizing of the whole card (e.g., on Hover) */
.js-post-card {
    transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    will-change: transform, box-shadow; /* Forces GPU acceleration */
}

.js-post-card:hover {
    transform: scale(1.015) translateY(-2px);
    box-shadow: 0 .5rem 1rem rgba(0,0,0,.1) !important;
    z-index: 2; /* Keeps scaled card above others */
}

/* 2. Smooth Height Expansion (for Reply form or Read More collapsing) */
/* Replaces standard Bootstrap collapse for smoother animation */
.collapse:not(.show) {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.3s ease-out;
}

.collapse.show {
    display: grid;
    grid-template-rows: 1fr;
    transition: grid-template-rows 0.3s ease-in;
}

/* The inner div must have overflow hidden for the grid trick to work */
.collapse > div {
    overflow: hidden;
}
</style>
<div class="js-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isReview ? 'd-flex flex-row align-items-stretch' : '' }}" 
     data-post-id="{{ $post->id }}" 
     data-is-review="{{ $isReview ? 'true' : 'false' }}">

    @if($isReview && $post->review->type === 'recommendation')
        <x-post.rating :rating="$post->review->rating ?? 0" />
    @endif

    <div class="w-100 flex-grow-1 d-flex flex-column {{ $isReview ? 'bg-white' : '' }}">
        <x-post.header :post="$post" />

        <div class="card-body pt-2 position-relative spoiler-container">
            @if($post->trashed())
                <p class="card-text text-muted fst-italic">[This post has been deleted]</p>
            @else
                @if($post->is_spoiler)
                    <div class="position-absolute top-0 start-0 w-100 h-100 d-flex justify-content-center align-items-center bg-dark bg-opacity-75 text-white rounded spoiler-overlay">
                        <div class="text-center"><i class="bi bi-eye-slash fs-3 mb-2 d-block"></i><span class="fw-bold">Spoiler Content</span></div>
                    </div>
                @endif

                <!-- Main Post Content -->
                <x-post.content :post="$post" />
                
                <!-- Quoted Parent Post (Twitter-style Quote) -->
                @if($post->parent_id && $post->parent)
                    <x-post.quote :post="$post->parent" />
                @endif

                <!-- Edit Form -->
                <x-post.edit-form :post="$post" />
            @endif
        </div>

        @if(!$post->trashed())
            <div class="js-post-footer card-footer bg-white border-top border-light d-flex justify-content-between align-items-center py-3">
                {{-- There will be 2 buttons. The one below that then slides down a text box (with attachment picker) that allows user to reply to this post
                adds a form FOR LOGGED USERS that lets user create a post --}}
                <button class="btn btn-light rounded-pill border shadow-sm d-flex align-items-center gap-2"><i class="bi bi-chat"></i> Reply</button>
                {{-- the second one, minor one will just slide down the comments section. THIS COMMENT SECTION IS OUR MICRO COMPONENT THAT WE WILL DO RIGHT NOW.  --}}
                <x-like-button :post="$post" />
            </div>
        @endif
    </div>
</div>

@once
<script>
    
    // Helper to animate height during DOM changes
    const animateCardTransition = async (card, outgoingSelectors, incomingSelectors, domChangeCallback) => {
        // 1. Grab elements
        const el = (selector) => card.querySelector(selector);
        const outgoingEls = outgoingSelectors.map(el).filter(Boolean);
        const incomingEls = incomingSelectors.map(el).filter(Boolean);

        // 2. Lock current height to prevent snapping
        const startHeight = card.offsetHeight;
        card.style.height = `${startHeight}px`;
        card.style.overflow = 'hidden';

        // 3. Fade out outgoing content fast (150ms)
        const fadeOuts = outgoingEls.map(node => 
            node.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 150, fill: 'forwards' }).finished
        );
        await Promise.all(fadeOuts);

        // 4. Execute the DOM swap (adding/removing d-none)
        domChangeCallback();

        // 5. Briefly allow auto-height to calculate the new size, then lock it back
        card.style.height = 'auto';
        const endHeight = card.offsetHeight;
        card.style.height = `${startHeight}px`;

        // 6. Animate height to new size AND fade in new content simultaneously
        const heightAnim = card.animate([
            { height: `${startHeight}px` },
            { height: `${endHeight}px` }
        ], { duration: 300, easing: 'ease-in-out' });

        incomingEls.forEach(node => {
            node.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 300, easing: 'ease-out', fill: 'forwards' });
        });

        // 7. Cleanup inline styles after animation finishes
        await heightAnim.finished;
        card.style.height = ''; 
        card.style.overflow = '';
    };
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.querySelectorAll('.js-post-card').forEach(card => {
            const postId = card.dataset.postId;
            const isReview = card.dataset.isReview === 'true';
            const el = (selector) => card.querySelector(selector);
            
            const viewMode = el('.js-view-mode');
            const editMode = el('.js-edit-mode');
            let activeMediaIds = Array.from(el('.js-edit-media-gallery')?.querySelectorAll('.media-edit-item') || []).map(item => item.dataset.mediaId);

            const toggleEditMode = (show) => {
                viewMode?.classList.toggle('d-none', show);
                el('.js-post-footer')?.classList.toggle('d-none', show);
                editMode?.classList.toggle('d-none', !show);
                el('.js-editing-badge')?.classList.toggle('d-none', !show);
                el('.js-rating-overlay')?.classList.toggle('d-none', !show);
            };

            card.addEventListener('click', async (e) => {
                // Helper function scoped to this card to keep selection accurate
                const el = (selector) => card.querySelector(selector);

                // Lightbox
                const trigger = e.target.closest('.js-lightbox-trigger');
                if (trigger && window.openGlobalLightbox) {
                    const fullMediaArray = JSON.parse(trigger.closest('.js-media-container').dataset.fullMedia);
                    window.openGlobalLightbox(fullMediaArray, trigger.dataset.index);
                }

                // Read More
                if (e.target.closest('.js-btn-read-more')) {
                    const btn = e.target.closest('.js-btn-read-more');
                    const textWrapper = el('.js-text-wrapper');

                    // 1. Measure the current clamped height
                    const startHeight = textWrapper.offsetHeight;
                    
                    // 2. Remove the clamp class and hide the button
                    textWrapper.classList.remove('text-truncate-container'); 
                    btn.style.display = 'none';
                    
                    // 3. Measure the new, fully expanded height
                    const endHeight = textWrapper.offsetHeight;
                    
                    // 4. Animate ONLY the text wrapper. The rest of the card will follow naturally!
                    const anim = textWrapper.animate([
                        { height: `${startHeight}px`, overflow: 'hidden' },
                        { height: `${endHeight}px`, overflow: 'hidden' }
                    ], { 
                        duration: 300, 
                        easing: 'ease-in-out' 
                    });

                    // Optional: Clean up inline styles after the animation finishes
                    anim.onfinish = () => {
                        textWrapper.style.height = '';
                        textWrapper.style.overflow = '';
                    };
                }
                
                // Edit Toggles
                if (e.target.closest('.js-btn-edit')) {
                    animateCardTransition(
                        card, 
                        ['.js-view-mode', '.js-post-footer'], // Outgoing elements
                        ['.js-edit-mode'],                    // Incoming elements
                        () => toggleEditMode(true)            // DOM swap function
                    );
                }
                if (e.target.closest('.js-btn-cancel')) {
                    animateCardTransition(
                        card, 
                        ['.js-edit-mode'],                    // Outgoing elements
                        ['.js-view-mode', '.js-post-footer'], // Incoming elements
                        () => toggleEditMode(false)           // DOM swap function
                    );
                }

                // Delete Media from gallery
                if (e.target.closest('.remove-icon')) {
                    const item = e.target.closest('.media-edit-item');
                    activeMediaIds = activeMediaIds.filter(id => id !== item.dataset.mediaId);
                    item.remove();
                }

                // Delete Post
                if (e.target.closest('.js-btn-delete')) {
                    if (!confirm('Permanently delete this post?')) return;
                    fetch(`/posts/${postId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrfToken } })
                        .then(res => { if(res.ok) { card.style.opacity = 0; setTimeout(() => card.remove(), 400); } });
                }

                // Upload Media
                if (e.target.closest('.js-btn-upload')) {
                    const uploadInput = el('.js-upload-input');
                    const file = uploadInput.files[0];
                    if (!file) return;

                    const btnUpload = el('.js-btn-upload');
                    btnUpload.disabled = true;
                    el('.js-upload-spinner').classList.remove('d-none');

                    const formData = new FormData(); formData.append('file', file);
                    fetch("{{ route('upload') ?? '/upload' }}", { method: 'POST', body: formData, headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' } })
                        .then(res => res.json())
                        .then(data => {
                            activeMediaIds.push(data.media.id);
                            el('.js-edit-media-gallery').insertAdjacentHTML('beforeend', `<div class="col-auto position-relative media-edit-item" data-media-id="${data.media.id}"><img src="${data.media.file_path}" style="height:80px; width:80px; object-fit:cover;"><div class="position-absolute top-0 end-0 bg-danger text-white rounded-circle d-flex align-items-center justify-content-center remove-icon pointer-events-none" style="width:20px; height:20px; transform:translate(30%,-30%);"><i class="bi bi-x small"></i></div></div>`);
                            uploadInput.value = '';
                        }).finally(() => { btnUpload.disabled = false; el('.js-upload-spinner').classList.add('d-none'); });
                }

                // Save Post
                if (e.target.closest('.js-btn-save')) {
                    const btnSave = el('.js-btn-save');
                    btnSave.disabled = true;
                    el('.js-save-spinner').classList.remove('d-none');

                    // Extract values from the inputs
                    const isSpoilerChecked = el('.js-edit-spoiler')?.checked;
                    const isLockedChecked = el('.js-edit-locked')?.checked;

                    const payload = { 
                        body: el('.js-edit-textarea').value, 
                        media_ids: activeMediaIds,
                        // Convert boolean state into 1 or 0 for standard database compatibility
                        is_spoiler: isSpoilerChecked ? 1 : 0,
                        is_locked: isLockedChecked ? 1 : 0
                    };
                    
                    if (isReview && el('.js-meter-text')) payload.rating = parseInt(el('.js-meter-text').innerText);
                    
                    fetch(`/posts/${postId}`, { 
                        method: 'PUT', 
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken }, 
                        body: JSON.stringify(payload) 
                    })
                    .then(res => { 
                        if(res.ok) {
                            window.location.reload(); 
                        } else {
                            btnSave.disabled = false;
                            el('.js-save-spinner').classList.add('d-none');
                        }
                    })
                    .catch(() => {
                        btnSave.disabled = false;
                        el('.js-save-spinner').classList.add('d-none');
                    });
                }
            });

            // Rating Drag Logic
            const ratingOverlay = el('.js-rating-overlay');
            if (isReview && ratingOverlay) {
                let isDragging = false;
                const updateRating = (e) => {
                    const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                    const rect = ratingOverlay.getBoundingClientRect();
                    const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
                    
                    const newRating = Math.round(percent * 10);
                    const fill = el('.js-meter-fill');
                    fill.style.height = `${newRating * 10}%`;
                    el('.js-meter-text').innerText = `${newRating} / 10`;
                    fill.className = `js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition ${newRating >= 7 ? 'bg-success' : (newRating >= 4 ? 'bg-warning' : 'bg-danger')}`;
                };
                const toggleDrag = state => e => { isDragging = state; if (state) updateRating(e); if (e.cancelable) e.preventDefault(); };
                const onDrag = e => { if (isDragging) { updateRating(e); if (e.cancelable) e.preventDefault(); } };

                ratingOverlay.addEventListener('mousedown', toggleDrag(true)); document.addEventListener('mousemove', onDrag); document.addEventListener('mouseup', toggleDrag(false));
                ratingOverlay.addEventListener('touchstart', toggleDrag(true), { passive: false }); document.addEventListener('touchmove', onDrag, { passive: false }); document.addEventListener('touchend', toggleDrag(false));
            }
        });
    });
</script>
@endonce