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

{{-- Add data-open="false" here to track the state --}}
<div class="js-edit-container" data-open="false">
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
                <x-post.form-toggles 
                    :spoilerClass="'js-edit-spoiler'" 
                    :lockClass="'js-edit-locked'"
                    :spoilerId="'isSpoiler-' . $post->id"
                    :lockId="'isLocked-' . $post->id"
                    :spoilerChecked="$post->is_spoiler"
                    :lockChecked="$post->is_locked"
                />
                
                <x-post.form-actions 
                    :clearClass="'js-btn-cancel'"
                    :submitClass="'js-btn-save'"
                    :spinnerClass="'js-save-spinner'"
                    :clearLabel="'Cancel'"
                    :submitLabel="'Save Changes'"
                />
            </div>
        </div>
    </div>
</div>

@once
<script>
// 1. Listen for the custom event to handle the animations
// 1. Listen for the custom event to handle the animations
document.addEventListener('toggle-edit', (e) => {
    const editContainer = e.target;
    const card = e.detail.card;
    const viewContainer = card.querySelector('.edit_form_collapsable');
    
    if (!viewContainer) return;

    const isOpen = editContainer.dataset.open === 'true';

    if (!isOpen) {
        // Open Edit Mode (Collapse View, Expand Edit)
        viewContainer.style.maxHeight = viewContainer.scrollHeight + 'px';
        viewContainer.offsetHeight; // Force reflow
        viewContainer.style.maxHeight = '0';
        viewContainer.style.opacity = '0';

        editContainer.style.maxHeight = editContainer.scrollHeight + 'px';
        editContainer.style.opacity = '1';
        editContainer.dataset.open = 'true';
    } else {
        // Close Edit Mode (Expand View, Collapse Edit)
        // 1. Transition the view container to its explicit pixel height
        viewContainer.style.maxHeight = viewContainer.scrollHeight + 'px';
        viewContainer.style.opacity = '1';

        // 2. Collapse the edit container smoothly
        editContainer.style.maxHeight = '0';
        editContainer.style.opacity = '0';
        editContainer.dataset.open = 'false';

        // 3. Wait for the transition to finish before resetting to auto ('')
        // This keeps your layout responsive if the window resizes later.
        viewContainer.addEventListener('transitionend', function handler() {
            // Guard check: ensure the user didn't quickly re-open it during the transition
            if (editContainer.dataset.open === 'false') {
                viewContainer.style.maxHeight = '';
            }
            viewContainer.removeEventListener('transitionend', handler);
        });
    }
});

// 2. Allow the "Cancel" button to fire the toggle event to close itself
document.addEventListener('click', (e) => {
    const cancelBtn = e.target.closest('.js-btn-cancel');
    if (!cancelBtn) return;

    e.preventDefault();
    const card = cancelBtn.closest('.js-post-card');
    const editContainer = cancelBtn.closest('.js-edit-container');

    if (card && editContainer) {
        editContainer.dispatchEvent(new CustomEvent('toggle-edit', { 
            bubbles: true, 
            detail: { card } 
        }));
    }
});
</script>
@endonce