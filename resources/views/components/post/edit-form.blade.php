@props(['post'])

<style>
/* Transition setup for the view container */
.edit_form_collapsable {
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    overflow: hidden;
}

/* Transition setup for this edit container */
.js-edit-container {
    transition: max-height 0.3s ease-out, opacity 0.3s ease-out;
    max-height: 0;
    opacity: 0;
    overflow: hidden;
}
</style>

{{-- Edit Form Wrapper --}}
<div class="js-edit-container">
    <div class="js-edit-mode border-top border-light p-3 bg-light">
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
</div>

@once
<script>
document.addEventListener('click', async (e) => {
    const card = e.target.closest('.js-post-card');
    if (!card) return;

    const el = (sel) => card.querySelector(sel);
    const postId = card.dataset.postId;
    
    // Target the collapsible view container & this edit container
    const viewContainer = el('.edit_form_collapsable');
    const editContainer = el('.js-edit-container');
    if (!viewContainer || !editContainer) return;

    const toggleState = (isEditing) => {
        if (isEditing) {
            // 1. Collapse View Mode
            viewContainer.style.maxHeight = viewContainer.scrollHeight + 'px';
            viewContainer.offsetHeight; // Force reflow for smooth transition
            viewContainer.style.maxHeight = '0';
            viewContainer.style.opacity = '0';

            // 2. Expand Edit Form
            editContainer.style.maxHeight = editContainer.scrollHeight + 'px';
            editContainer.style.opacity = '1';
        } else {
            // 1. Expand View Mode
            viewContainer.style.maxHeight = viewContainer.scrollHeight + 'px';
            viewContainer.offsetHeight;
            viewContainer.style.maxHeight = ''; // Reset to auto
            viewContainer.style.opacity = '1';

            // 2. Collapse Edit Form
            editContainer.style.maxHeight = '0';
            editContainer.style.opacity = '0';
        }
    };

    // Trigger: Edit Button (from Menu)
    if (e.target.closest('.js-btn-edit')) {
        e.preventDefault(); e.stopPropagation();
        toggleState(true);
        return;
    }

    // Trigger: Cancel Button
    if (e.target.closest('.js-btn-cancel')) {
        e.preventDefault(); e.stopPropagation();
        toggleState(false);
        return;
    }

    // Trigger: Save Button
    if (e.target.closest('.js-btn-save')) {
        const btnSave = e.target.closest('.js-btn-save');
        btnSave.disabled = true;
        el('.js-save-spinner').classList.remove('d-none');

        const mediaIds = Array.from(card.querySelectorAll('input[name="media_ids[]"]')).map(input => input.value);
        const payload = { 
            body: el('.js-edit-textarea').value, 
            media_ids: mediaIds,
            is_spoiler: el('.js-edit-spoiler')?.checked ? 1 : 0,
            is_locked: el('.js-edit-locked')?.checked ? 1 : 0
        };
        
        const meter = card.querySelector('.rating-meter-container');
        if (meter && meter.dataset.currentRating) payload.rating = parseInt(meter.dataset.currentRating, 10);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
        try {
            const res = await fetch(`/posts/${postId}`, { 
                method: 'PUT', 
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, 
                body: JSON.stringify(payload) 
            });
            
            if(res.ok) window.location.reload(); 
            else throw new Error((await res.json())?.message || 'Failed to save');
        } catch (err) {
            console.error(err);
            alert('Failed to save: ' + err.message);
            btnSave.disabled = false;
            el('.js-save-spinner').classList.add('d-none');
        }
    }
});
</script>
@endonce
