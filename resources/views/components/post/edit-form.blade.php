@props(['post'])

<div class="js-edit-mode d-none border rounded p-3 bg-light">
    <div class="flex-grow-1">
        <div class="mb-3">
            <label class="form-label fw-bold text-muted small">Post Content</label>
            <textarea class="js-edit-textarea form-control" rows="4">{{ $post->body }}</textarea>
        </div>

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

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
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

            <div class="d-flex gap-2 justify-content-end">
                <button class="js-btn-cancel btn btn-outline-secondary">Cancel</button>
                <button class="js-btn-save btn btn-primary px-4"><span class="js-save-spinner spinner-border spinner-border-sm d-none"></span> Save Changes</button>
            </div>
        </div>
    </div>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    document.addEventListener('click', async (e) => {
        const card = e.target.closest('.js-post-card');
        if (!card) return;

        const el = (selector) => card.querySelector(selector);
        const postId = card.dataset.postId;
        const isReview = card.dataset.isReview === 'true';

        const toggleEditMode = (show) => {
            const viewMode = el('.js-view-mode');
            const editMode = el('.js-edit-mode');
            const footer = el('.js-post-footer');
            const badge = el('.js-editing-badge');

            if (show) {
                card.classList.add('is-editing'); // <-- Signal: Enables rating meter drag via CSS
                
                viewMode?.classList.add('d-none');
                footer?.classList.add('d-none');
                editMode?.classList.remove('d-none');
                badge?.classList.remove('d-none');
            } else {
                card.classList.remove('is-editing'); // <-- Removes Signal
                
                viewMode?.classList.remove('d-none');
                footer?.classList.remove('d-none');
                editMode?.classList.add('d-none');
                badge?.classList.add('d-none');
                
                // Dispatch event so decoupled components (like rating meter) can reset themselves
                card.dispatchEvent(new CustomEvent('post:cancel-edit'));
            }
        };

        // Trigger Edit
        if (e.target.closest('.js-btn-edit')) {
            e.preventDefault();
            e.stopPropagation();
            toggleEditMode(true);
        }

        // Trigger Cancel
        if (e.target.closest('.js-btn-cancel')) {
            e.preventDefault();
            e.stopPropagation();
            toggleEditMode(false);
        }

        // Saving Form Data
        if (e.target.closest('.js-btn-save')) {
            const btnSave = e.target.closest('.js-btn-save');
            btnSave.disabled = true;
            el('.js-save-spinner').classList.remove('d-none');

            const mediaIds = Array.from(card.querySelectorAll('input[name="media_ids[]"]'))
                                  .map(input => input.value);

            const payload = { 
                body: el('.js-edit-textarea').value, 
                media_ids: mediaIds,
                is_spoiler: el('.js-edit-spoiler')?.checked ? 1 : 0,
                is_locked: el('.js-edit-locked')?.checked ? 1 : 0
            };
            
            // Decoupled Rating Extraction: Simply check if a meter exists and grab the value
            const meter = card.querySelector('.rating-meter-container');
            if (meter && meter.dataset.currentRating) {
                payload.rating = parseInt(meter.dataset.currentRating, 10);
            }
            
            try {
                const res = await fetch(`/posts/${postId}`, { 
                    method: 'PUT', 
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': csrfToken, 
                        'Accept': 'application/json' 
                    }, 
                    body: JSON.stringify(payload) 
                });
                
                if(res.ok) {
                    window.location.reload(); 
                } else {
                    const errorData = await res.json();
                    throw new Error(errorData.message || 'Failed to save');
                }
            } catch (err) {
                console.error(err);
                alert('Failed to save changes: ' + err.message);
                btnSave.disabled = false;
                el('.js-save-spinner').classList.add('d-none');
            }
        }
    });
});
</script>
@endonce