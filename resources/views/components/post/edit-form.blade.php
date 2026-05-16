@props(['post'])

<div class="js-edit-mode d-none border rounded p-3 bg-light">
    <!-- Post Content -->
    <div class="mb-3">
        <label class="form-label fw-bold text-muted small">Post Content</label>
        <textarea class="js-edit-textarea form-control" rows="4">{{ $post->body }}</textarea>
    </div>

    <!-- Media (Handled entirely by the new component) -->
    <div class="mb-3 border-bottom pb-3">
        <label class="form-label fw-bold text-muted small">Media</label>
        <x-media-upload 
            multiple="true" 
            inputName="media_ids[]" 
            accept="image/*,video/mp4" 
            :existingMedia="$post->media"
            previewClass="rounded border bg-dark"
            previewStyle="height: 80px; width: 80px; object-fit: cover;"
        />
    </div>

    <!-- Toggles & Action Buttons Row -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
        <!-- Toggles (Left Side) -->
        <div class="d-flex gap-3">
            <div class="form-check form-switch mb-0">
                <input class="form-check-input js-edit-spoiler" type="checkbox" id="isSpoiler-{{ $post->id }}" {{ $post->is_spoiler ? 'checked' : '' }}>
                <label class="form-check-label small user-select-none" for="isSpoiler-{{ $post->id }}">
                    <i class="bi bi-eye-slash-fill text-warning me-1"></i> Spoiler
                </label>
            </div>

            <div class="form-check form-switch mb-0">
                <input class="form-check-input js-edit-locked" type="checkbox" id="isLocked-{{ $post->id }}" {{ $post->is_locked ? 'checked' : '' }}>
                <label class="form-check-label small user-select-none" for="isLocked-{{ $post->id }}">
                    <i class="bi bi-lock-fill text-danger me-1"></i> Lock
                </label>
            </div>
        </div>

        <!-- Buttons (Right Side) -->
        <div class="d-flex gap-2 justify-content-end">
            <button class="js-btn-cancel btn btn-outline-secondary">Cancel</button>
            <button class="js-btn-save btn btn-primary px-4"><span class="js-save-spinner spinner-border spinner-border-sm d-none"></span> Save Changes</button>
        </div>
    </div>
</div>

@once
<script>
    window.animateCardTransition = async (card, outgoingSelectors, incomingSelectors, domChangeCallback) => {
        const el = (selector) => card.querySelector(selector);
        const outgoingEls = outgoingSelectors.map(el).filter(Boolean);
        const incomingEls = incomingSelectors.map(el).filter(Boolean);

        const startHeight = card.offsetHeight;
        card.style.height = `${startHeight}px`;
        card.style.overflow = 'hidden';

        const fadeOuts = outgoingEls.map(node => 
            node.animate([{ opacity: 1 }, { opacity: 0 }], { duration: 150, fill: 'forwards' }).finished
        );
        await Promise.all(fadeOuts);

        domChangeCallback();

        card.style.height = 'auto';
        const endHeight = card.offsetHeight;
        card.style.height = `${startHeight}px`;

        const heightAnim = card.animate([
            { height: `${startHeight}px` },
            { height: `${endHeight}px` }
        ], { duration: 300, easing: 'ease-in-out' });

        incomingEls.forEach(node => {
            node.animate([{ opacity: 0 }, { opacity: 1 }], { duration: 300, easing: 'ease-out', fill: 'forwards' });
        });

        await heightAnim.finished;
        card.style.height = ''; 
        card.style.overflow = '';
    };

    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.addEventListener('click', async (e) => {
            const card = e.target.closest('.js-post-card');
            if (!card) return;

            const el = (selector) => card.querySelector(selector);
            const postId = card.dataset.postId;
            const isReview = card.dataset.isReview === 'true';

            const toggleEditMode = (show) => {
                el('.js-view-mode')?.classList.toggle('d-none', show);
                el('.js-post-footer')?.classList.toggle('d-none', show);
                el('.js-edit-mode')?.classList.toggle('d-none', !show);
                el('.js-editing-badge')?.classList.toggle('d-none', !show);
                el('.js-rating-overlay')?.classList.toggle('d-none', !show);
            };

            // Trigger Edit
            if (e.target.closest('.js-btn-edit')) {
                if (window.animateCardTransition) {
                    window.animateCardTransition(card, ['.js-view-mode', '.js-post-footer'], ['.js-edit-mode'], () => toggleEditMode(true));
                } else {
                    toggleEditMode(true);
                }
            }

            // Trigger Cancel
            if (e.target.closest('.js-btn-cancel')) {
                if (window.animateCardTransition) {
                    window.animateCardTransition(card, ['.js-edit-mode'], ['.js-view-mode', '.js-post-footer'], () => toggleEditMode(false));
                } else {
                    toggleEditMode(false);
                }
            }

            // Saving Form Data
            if (e.target.closest('.js-btn-save')) {
                const btnSave = e.target.closest('.js-btn-save');
                btnSave.disabled = true;
                el('.js-save-spinner').classList.remove('d-none');

                // NEW: Grab media IDs directly from the hidden inputs generated by the uploader component
                const mediaIds = Array.from(card.querySelectorAll('input[name="media_ids[]"]'))
                                      .map(input => input.value);

                const payload = { 
                    body: el('.js-edit-textarea').value, 
                    media_ids: mediaIds,
                    is_spoiler: el('.js-edit-spoiler')?.checked ? 1 : 0,
                    is_locked: el('.js-edit-locked')?.checked ? 1 : 0
                };
                
                if (isReview && el('.js-meter-text')) {
                    payload.rating = parseInt(el('.js-meter-text').innerText);
                }
                
                fetch(`/posts/${postId}`, { 
                    method: 'PUT', 
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, 
                    body: JSON.stringify(payload) 
                })
                .then(async res => { 
                    if(res.ok) {
                        window.location.reload(); 
                    } else {
                        const errorData = await res.json();
                        throw new Error(errorData.message || 'Failed to save');
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert('Failed to save changes: ' + err.message);
                    btnSave.disabled = false;
                    el('.js-save-spinner').classList.add('d-none');
                });
            }
        });
    });
</script>
@endonce