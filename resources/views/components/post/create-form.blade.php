@props([
    'hubType' => null,
    'hubId' => null,
    'parentId' => null,
    'reviewType' => null
])

@php
    $isRecommendation = $reviewType === 'recommendation';
    $uid = \Illuminate\Support\Str::random(8);
@endphp

<div class="js-create-post-card card shadow-sm mb-4 border-0 overflow-hidden {{ $isRecommendation ? 'd-flex flex-row align-items-stretch' : '' }}"
    data-hub-type="{{ $hubType }}"
    data-hub-id="{{ $hubId }}"
    data-parent-id="{{ $parentId }}"
    data-review-type="{{ $reviewType }}">

    @if($isRecommendation)
        {{-- Look how clean this is now! We just pass editable="true" and a starting rating of 10 --}}
        <x-post.rating-meter :rating="10" editable="true" />
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

        // Note: All the complex drag logic is completely gone from here!
        // It now lives exclusively inside the rating-meter component.

        // Global Event Delegation for Creation Interactions
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
                
                // Reset meter visually if it exists
                const meter = el('.rating-meter-container');
                if (meter) {
                    const fill = meter.querySelector('.js-meter-fill');
                    const text = meter.querySelector('.js-meter-text');
                    
                    meter.dataset.currentRating = 10;
                    if (fill && text) {
                        fill.style.height = `100%`;
                        text.innerText = `10 / 10`;
                        fill.className = `js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition bg-success`;
                    }
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
                
                // Decoupled Rating Extraction
                const reviewType = card.dataset.reviewType;
                if (reviewType) {
                    payload.review_type = reviewType;
                    
                    if (reviewType === 'recommendation') {
                        const meter = el('.rating-meter-container');
                        if (meter && meter.dataset.currentRating) {
                            payload.rating = parseInt(meter.dataset.currentRating, 10);
                        }
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