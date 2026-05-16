@props([
    'hubType' => null,
    'hubId' => null,
    'parentId' => null
])

@php
    $uid = \Illuminate\Support\Str::random(8);
@endphp

<div class="js-create-comment-card card shadow-sm mb-3 border-0 overflow-hidden bg-white"
    data-hub-type="{{ $hubType }}"
    data-hub-id="{{ $hubId }}"
    data-parent-id="{{ $parentId }}">

    <div class="p-3">
        <div class="mb-3">
            <label class="form-label fw-bold text-muted small">Reply as a comment...</label>
            <textarea class="js-comment-textarea form-control" rows="3" placeholder="Write your reply..."></textarea>
        </div>

        <div class="mb-3 border-bottom pb-3">
            <label class="form-label fw-bold text-muted small">Media</label>
            <x-media-upload 
                multiple="true" 
                inputName="comment_media_ids[]" 
                accept="image/*,video/mp4" 
                previewClass="rounded border bg-dark"
                previewStyle="height: 80px; width: 80px; object-fit: cover;"
            />
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
            <div class="d-flex gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input js-comment-spoiler" type="checkbox" id="commentSpoiler-{{ $uid }}">
                    <label class="form-check-label small user-select-none" for="commentSpoiler-{{ $uid }}">
                        <i class="bi bi-eye-slash-fill text-warning me-1"></i> Spoiler
                    </label>
                </div>

                <div class="form-check form-switch mb-0">
                    <input class="form-check-input js-comment-locked" type="checkbox" id="commentLocked-{{ $uid }}">
                    <label class="form-check-label small user-select-none" for="commentLocked-{{ $uid }}">
                        <i class="bi bi-lock-fill text-danger me-1"></i> Lock
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                <button class="js-btn-comment-clear btn btn-outline-secondary btn-sm">Clear</button>
                <button class="js-btn-comment-submit btn btn-primary btn-sm px-3">
                    <span class="js-comment-submit-spinner spinner-border spinner-border-sm d-none"></span> Reply
                </button>
            </div>
        </div>
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

        document.addEventListener('click', async (e) => {
            // Scope strictly to comment form to avoid interfering with main post form
            const card = e.target.closest('.js-create-comment-card');
            if (!card) return;

            const el = (selector) => card.querySelector(selector);

            // Clear Button Logic
            if (e.target.closest('.js-btn-comment-clear')) {
                e.preventDefault();
                if (el('.js-comment-textarea')) el('.js-comment-textarea').value = '';
                if (el('.js-comment-spoiler')) el('.js-comment-spoiler').checked = false;
                if (el('.js-comment-locked')) el('.js-comment-locked').checked = false;
                return;
            }

            // Submit Logic
            if (e.target.closest('.js-btn-comment-submit')) {
                e.preventDefault();
                const btnSubmit = e.target.closest('.js-btn-comment-submit');
                btnSubmit.disabled = true;
                el('.js-comment-submit-spinner').classList.remove('d-none');

                const mediaIds = Array.from(card.querySelectorAll('input[name="comment_media_ids[]"]'))
                                      .map(input => input.value);

                const payload = { 
                    hub_type: card.dataset.hubType || null,
                    hub_id: card.dataset.hubId || null,
                    parent_id: card.dataset.parentId || null,
                    body: el('.js-comment-textarea').value, 
                    media_ids: mediaIds,
                    is_spoiler: el('.js-comment-spoiler')?.checked ? 1 : 0,
                    is_locked: el('.js-comment-locked')?.checked ? 1 : 0
                };

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
                        throw new Error(errorData.message || 'Failed to create comment');
                    }
                })
                .catch((err) => {
                    console.error(err);
                    alert('Error: ' + err.message);
                    btnSubmit.disabled = false;
                    el('.js-comment-submit-spinner').classList.add('d-none');
                });
            }
        });
    });
</script>
@endonce
