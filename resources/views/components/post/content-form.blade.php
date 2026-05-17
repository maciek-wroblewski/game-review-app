@props([
'mode' => 'create', // 'create' | 'edit'
'type' => 'post', // 'post' | 'comment'
'post' => null, // Passed when mode='edit'
'reviewType' => null, // e.g., 'recommendation'
'hubType' => null,
'hubId' => null,
'parentId' => null,
'mediaInputName' => null, // Fallbacks to sensible defaults
])

@php
$isEdit = $mode === 'edit';
$isComment = $type === 'comment';
$hasRating = $reviewType === 'recommendation';
$inputName = $mediaInputName ?? ($isComment ? 'comment_media_ids[]' : 'create_media_ids[]');
@endphp

<div class="js-content-form-card card shadow-sm mb-3 border-0 overflow-hidden bg-white" data-mode="{{ $mode }}"
    data-type="{{ $type }}" data-post-id="{{ $isEdit ? $post->id : null }}" data-hub-type="{{ $hubType }}"
    data-hub-id="{{ $hubId }}" data-parent-id="{{ $parentId }}" data-review-type="{{ $reviewType }}">

    @if($hasRating && !$isEdit)
    <x-post.rating-meter :rating="10" editable="true" />
    @endif

    <div class="p-3">
        <div class="mb-3">
            <label class="form-label fw-bold text-muted small">
                {{ $isComment ? 'Reply as a comment...' : ($isEdit ? 'Post Content' : 'Write a post...') }}
            </label>
            <textarea class="js-content-textarea form-control" rows="{{ $isComment ? 3 : 4 }}"
                placeholder="{{ $isComment ? 'Write your reply...' : " What's on your mind?" }}">
{{ old('body', $isEdit ? $post->body : '') }}</textarea>
        </div>

        <div class="mb-3 border-bottom pb-3">
            <label class="form-label fw-bold text-muted small">Media</label>
            <x-media-upload multiple="true" inputName="{{ $inputName }}" accept="image/*,video/mp4"
                :existingMedia="$post->media"/>
        </div>

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mt-3">
            <div class="d-flex gap-3">
                <div class="form-check form-switch mb-0">
                    <input class="js-content-spoiler form-check-input" type="checkbox"
                        id="spoiler-{{ $post->id ?? \Illuminate\Support\Str::random(6) }}" {{ $isEdit &&
                        $post->is_spoiler ? 'checked' : '' }}>
                    <label class="form-check-label small user-select-none"
                        for="spoiler-{{ $post->id ?? \Illuminate\Support\Str::random(6) }}">
                        <i class="bi bi-eye-slash-fill text-warning me-1"></i> Spoiler
                    </label>
                </div>
                <div class="form-check form-switch mb-0">
                    <input class="js-content-locked form-check-input" type="checkbox"
                        id="locked-{{ $post->id ?? \Illuminate\Support\Str::random(6) }}" {{ $isEdit && $post->is_locked
                    ? 'checked' : '' }}>
                    <label class="form-check-label small user-select-none"
                        for="locked-{{ $post->id ?? \Illuminate\Support\Str::random(6) }}">
                        <i class="bi bi-lock-fill text-danger me-1"></i> Lock
                    </label>
                </div>
            </div>

            <div class="d-flex gap-2 justify-content-end">
                @if($isEdit)
                <button class="js-btn-cancel btn btn-outline-secondary">Cancel</button>
                <button class="js-btn-submit btn btn-primary px-4">
                    <span class="js-submit-spinner spinner-border spinner-border-sm d-none"></span> Save Changes
                </button>
                @else
                <button class="js-btn-clear btn btn-outline-secondary btn-sm">Clear</button>
                <button class="js-btn-submit btn btn-primary btn-sm px-3">
                    <span class="js-submit-spinner spinner-border spinner-border-sm d-none"></span> {{ $isComment ?
                    'Reply' : 'Post' }}
                </button>
                @endif
            </div>
        </div>
    </div>
</div>

@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    document.addEventListener('click', async (e) => {
        const card = e.target.closest('.js-content-form-card');
        if (!card) return;

        const el = (sel) => card.querySelector(sel);
        const isEdit = card.dataset.mode === 'edit';
        const btnSubmit = el('.js-btn-submit');
        const btnCancel = el('.js-btn-cancel');
        const btnClear = el('.js-btn-clear');

        // 1. Clear / Cancel Logic
        if (e.target.closest('.js-btn-clear') || e.target.closest('.js-btn-cancel')) {
            e.preventDefault();
            if (el('.js-content-textarea')) el('.js-content-textarea').value = '';
            if (el('.js-content-spoiler')) el('.js-content-spoiler').checked = false;
            if (el('.js-content-locked')) el('.js-content-locked').checked = false;
            
            // Reset rating meter if present
            const meter = el('.rating-meter-container');
            if (meter) {
                const fill = meter.querySelector('.js-meter-fill');
                const text = meter.querySelector('.js-meter-text');
                meter.dataset.currentRating = 10;
                if (fill && text) {
                    fill.style.height = '100%';
                    text.innerText = '10 / 10';
                    fill.className = 'js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition bg-success';
                }
            }
            
            // If edit mode, trigger parent collapse logic (if you keep it separate)
            if (isEdit && btnCancel) {
                card.dispatchEvent(new CustomEvent('form-cancelled', { bubbles: true }));
            }
            return;
        }

        // 2. Submit Logic
        if (e.target.closest('.js-btn-submit')) {
            e.preventDefault();
            btnSubmit.disabled = true;
            el('.js-submit-spinner').classList.remove('d-none');

            const mediaIds = Array.from(card.querySelectorAll('input[name*="media_ids"]'))
                                  .map(input => input.value);

            const payload = { 
                hub_type: card.dataset.hubType || null,
                hub_id: card.dataset.hubId || null,
                parent_id: card.dataset.parentId || null,
                body: el('.js-content-textarea').value, 
                media_ids: mediaIds,
                is_spoiler: el('.js-content-spoiler')?.checked ? 1 : 0,
                is_locked: el('.js-content-locked')?.checked ? 1 : 0
            };

            const reviewType = card.dataset.reviewType;
            if (reviewType === 'recommendation') {
                payload.review_type = reviewType;
                const meter = el('.rating-meter-container');
                if (meter?.dataset.currentRating) {
                    payload.rating = parseInt(meter.dataset.currentRating, 10);
                }
            }

            const endpoint = isEdit ? `/posts/${card.dataset.postId}` : '/posts';
            const method = isEdit ? 'PUT' : 'POST';

            try {
                const res = await fetch(endpoint, {
                    method,
                    headers: { 
                        'Content-Type': 'application/json', 
                        'X-CSRF-TOKEN': csrfToken, 
                        'Accept': 'application/json' 
                    },
                    body: JSON.stringify(payload)
                });

                if (res.ok) {
                    window.location.reload();
                } else {
                    const errData = await res.json();
                    throw new Error(errData.message || 'Request failed');
                }
            } catch (err) {
                console.error(err);
                alert('Error: ' + err.message);
                btnSubmit.disabled = false;
                el('.js-submit-spinner').classList.add('d-none');
            }
        }
    });
});

</script>
@endonce