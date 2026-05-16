@props([
    'hubType' => null,
    'hubId' => null,
    'parentId' => null,
    'reviewType' => null
])

@php
    $isRecommendation = $reviewType === 'recommendation';
    // Generate a unique ID for the toggle labels to prevent conflicts if rendered multiple times
    $uid = \Illuminate\Support\Str::random(8);
@endphp

<div class="js-create-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isRecommendation ? 'd-flex flex-row align-items-stretch' : '' }}"
    data-hub-type="{{ $hubType }}"
    data-hub-id="{{ $hubId }}"
    data-parent-id="{{ $parentId }}"
    data-review-type="{{ $reviewType }}">

    @if($isRecommendation)
    <div class="rating-meter-container position-relative d-flex flex-column justify-content-end border-end" style="width: 50px; min-width: 50px; background-color: rgba(0, 0, 0, 0.05); z-index: 1;">
        <div class="js-create-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition bg-success" style="height: 100%; transition: height 0.1s, background-color 0.3s;">
            <span class="js-create-meter-text" style="writing-mode: vertical-rl; transform: rotate(180deg); font-size: 0.85rem;">10 / 10</span>
        </div>
        <div class="js-create-rating-overlay position-absolute top-0 start-0 w-100 h-100 cursor-crosshair" style="z-index: 10;" title="Click and drag to set rating"></div>
    </div>
    @endif

    <div class="flex-grow-1 d-flex flex-column p-3 bg-white" style="min-width: 0;">
        <div class="mb-3">
            <label class="form-label fw-bold text-muted small">Write a post...</label>
            <textarea class="js-create-textarea form-control" rows="4" placeholder="What's on your mind?"></textarea>
        </div>

        <div class="mb-3 border-bottom pb-3">
            <label class="form-label fw-bold text-muted small">Media</label>
            <x-media-upload 
                multiple="true" 
                inputName="create_media_ids[]" 
                accept="image/*,video/mp4" 
                previewClass="rounded border bg-dark"
                previewStyle="height: 80px; width: 80px; object-fit: cover;"
            />
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
            <div class="d-flex gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input js-create-spoiler" type="checkbox" id="createSpoiler-{{ $uid }}">
                    <label class="form-check-label small user-select-none" for="createSpoiler-{{ $uid }}">
                        <i class="bi bi-eye-slash-fill text-warning me-1"></i> Spoiler
                    </label>
                </div>

                <div class="form-check form-switch mb-0">
                    <input class="form-check-input js-create-locked" type="checkbox" id="createLocked-{{ $uid }}">
                    <label class="form-check-label small user-select-none" for="createLocked-{{ $uid }}">
                        <i class="bi bi-lock-fill text-danger me-1"></i> Lock
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <button class="js-btn-create-clear btn btn-outline-secondary">Clear</button>
                <button class="js-btn-submit-post btn btn-primary px-4">
                    <span class="js-submit-spinner spinner-border spinner-border-sm d-none"></span> Post
                </button>
            </div>
        </div>
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        // 1. Drag Logic for the Recommendation Rating Meter
        document.querySelectorAll('.js-create-post-card').forEach(card => {
            const ratingOverlay = card.querySelector('.js-create-rating-overlay');
            if (!ratingOverlay) return;

            let isDragging = false;

            const updateRating = (e) => {
                const clientY = e.touches ? e.touches[0].clientY : e.clientY;
                const rect = ratingOverlay.getBoundingClientRect();
                const percent = Math.max(0.1, Math.min(1.0, 1 - ((clientY - rect.top) / rect.height)));
                
                const newRating = Math.round(percent * 10);
                const fill = card.querySelector('.js-create-meter-fill');
                const text = card.querySelector('.js-create-meter-text');
                
                if (fill && text) {
                    fill.style.height = `${newRating * 10}%`;
                    text.innerText = `${newRating} / 10`;
                    fill.className = `js-create-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition ${newRating >= 7 ? 'bg-success' : (newRating >= 4 ? 'bg-warning' : 'bg-danger')}`;
                }
            };

            const toggleDrag = state => e => { 
                isDragging = state; 
                if (state) updateRating(e); 
                if (e.cancelable) e.preventDefault(); 
            };

            const onDrag = e => { 
                if (isDragging) { 
                    updateRating(e); 
                    if (e.cancelable) e.preventDefault(); 
                } 
            };

            ratingOverlay.addEventListener('mousedown', toggleDrag(true));
            document.addEventListener('mousemove', onDrag);
            document.addEventListener('mouseup', toggleDrag(false));

            ratingOverlay.addEventListener('touchstart', toggleDrag(true), { passive: false });
            document.addEventListener('touchmove', onDrag, { passive: false });
            document.addEventListener('touchend', toggleDrag(false));
        });

        // 2. Global Event Delegation for Creation Interactions
        document.addEventListener('click', async (e) => {
            const card = e.target.closest('.js-create-post-card');
            if (!card) return;

            const el = (selector) => card.querySelector(selector);

            // Clear Button Logic
            if (e.target.closest('.js-btn-create-clear')) {
                e.preventDefault();
                if (el('.js-create-textarea')) el('.js-create-textarea').value = '';
                if (el('.js-create-spoiler')) el('.js-create-spoiler').checked = false;
                if (el('.js-create-locked')) el('.js-create-locked').checked = false;
                
                // Reset meter if it exists
                const fill = el('.js-create-meter-fill');
                const text = el('.js-create-meter-text');
                if (fill && text) {
                    fill.style.height = `100%`;
                    text.innerText = `10 / 10`;
                    fill.className = `js-create-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition bg-success`;
                }
                return;
            }

            // Submit Logic
            if (e.target.closest('.js-btn-submit-post')) {
                e.preventDefault();
                const btnSubmit = e.target.closest('.js-btn-submit-post');
                btnSubmit.disabled = true;
                el('.js-submit-spinner').classList.remove('d-none');

                const mediaIds = Array.from(card.querySelectorAll('input[name="create_media_ids[]"]'))
                                      .map(input => input.value);

                // Build Base Payload mapped directly to your database setup
                const payload = { 
                    hub_type: card.dataset.hubType || null,
                    hub_id: card.dataset.hubId || null,
                    parent_id: card.dataset.parentId || null,
                    body: el('.js-create-textarea').value, 
                    media_ids: mediaIds,
                    is_spoiler: el('.js-create-spoiler')?.checked ? 1 : 0,
                    is_locked: el('.js-create-locked')?.checked ? 1 : 0
                };
                
                // Append Review specific data if present
                const reviewType = card.dataset.reviewType;
                if (reviewType) {
                    payload.review_type = reviewType;
                    if (reviewType === 'recommendation' && el('.js-create-meter-text')) {
                        payload.rating = parseInt(el('.js-create-meter-text').innerText);
                    }
                }
                
                fetch(`/posts`, { 
                    method: 'POST', 
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': csrfToken, 
                        'Accept': 'application/json' 
                    }, 
                    body: JSON.stringify(payload) 
                })
                .then(async res => { 
                    if(res.ok) {
                        window.location.reload(); 
                    } else {
                        const errorData = await res.json();
                        throw new Error(errorData.message || 'Failed to create post');
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert('Error: ' + err.message);
                    btnSubmit.disabled = false;
                    el('.js-submit-spinner').classList.add('d-none');
                });
            }
        });
    });
</script>
@endonce