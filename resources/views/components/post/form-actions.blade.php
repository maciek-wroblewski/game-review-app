@props([
    'clearClass' => 'js-btn-create-clear',
    'submitClass' => 'js-btn-submit-post',
    'spinnerClass' => 'js-submit-spinner',
    'clearLabel' => 'Clear',
    'submitLabel' => 'Post'
])
<div class="d-flex gap-2 justify-content-end">
    <button class="{{ $clearClass }} btn btn-outline-secondary btn-sm">{{ $clearLabel }}</button>
    <button class="{{ $submitClass }} btn btn-primary btn-sm px-3">
        <span class="{{ $spinnerClass }} spinner-border spinner-border-sm d-none"></span> {{ $submitLabel }}
    </button>
</div>

@once
<script>
document.addEventListener('DOMContentLoaded', () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    const formConfigs = [
        {
            selector: '.js-create-comment-card',
            mediaInput: 'comment_media_ids[]',
            endpoint: '/posts',
            method: 'POST',
            selectors: {
                textarea: '.js-comment-textarea',
                spoiler: '.js-comment-spoiler',
                lock: '.js-comment-locked',
                clearBtn: '.js-btn-comment-clear',
                submitBtn: '.js-btn-comment-submit',
                spinner: '.js-comment-submit-spinner'
            }
        },
        {
            selector: '.js-create-post-card',
            mediaInput: 'create_media_ids[]',
            endpoint: '/posts',
            method: 'POST',
            selectors: {
                textarea: '.js-create-textarea',
                spoiler: '.js-create-spoiler',
                lock: '.js-create-locked',
                clearBtn: '.js-btn-create-clear',
                submitBtn: '.js-btn-submit-post',
                spinner: '.js-submit-spinner'
            },
            extraPayload: (card) => {
                const reviewType = card.dataset.reviewType;
                if (!reviewType) return {};
                const payload = { review_type: reviewType };
                if (reviewType === 'recommendation') {
                    const meter = card.querySelector('.rating-meter-container');
                    if (meter?.dataset.currentRating) payload.rating = parseInt(meter.dataset.currentRating, 10);
                }
                return payload;
            },
            resetMeter: (card) => {
                const meter = card.querySelector('.rating-meter-container');
                if (!meter) return;
                meter.dataset.currentRating = 10;
                const fill = meter.querySelector('.js-meter-fill');
                const text = meter.querySelector('.js-meter-text');
                if (fill && text) {
                    fill.style.height = '100%';
                    text.innerText = '10 / 10';
                    fill.className = 'js-meter-fill meter-fill w-100 d-flex align-items-start justify-content-center pt-2 text-white fw-bold style-transition bg-success';
                }
            }
        },
        {
            selector: '.js-post-card', // Edit form wrapper
            mediaInput: 'media_ids[]',
            endpoint: null, // Resolved dynamically
            method: 'PUT',
            selectors: {
                textarea: '.js-edit-textarea',
                spoiler: '.js-edit-spoiler',
                lock: '.js-edit-locked',
                clearBtn: '.js-btn-cancel',
                submitBtn: '.js-btn-save',
                spinner: '.js-save-spinner'
            },
            extraPayload: (card) => {
                const meter = card.querySelector('.rating-meter-container');
                const payload = {};
                if (meter?.dataset.currentRating) payload.rating = parseInt(meter.dataset.currentRating, 10);
                return payload;
            }
        }
    ];

    formConfigs.forEach(config => {
        document.addEventListener('click', async (e) => {
            const card = e.target.closest(config.selector);
            if (!card) return;
            const el = (sel) => card.querySelector(sel);

            // Clear / Cancel Logic
            if (e.target.closest(config.selectors.clearBtn)) {
                e.preventDefault();
                if (el(config.selectors.textarea)) el(config.selectors.textarea).value = '';
                if (el(config.selectors.spoiler)) el(config.selectors.spoiler).checked = false;
                if (el(config.selectors.lock)) el(config.selectors.lock).checked = false;
                if (config.resetMeter) config.resetMeter(card);
                return;
            }

            // Submit / Save Logic
            if (e.target.closest(config.selectors.submitBtn)) {
                e.preventDefault();
                const btn = e.target.closest(config.selectors.submitBtn);
                btn.disabled = true;
                if (el(config.selectors.spinner)) el(config.selectors.spinner).classList.remove('d-none');

                try {
                    const mediaIds = Array.from(card.querySelectorAll(`input[name="${config.mediaInput}"]`)).map(i => i.value);
                    const endpoint = config.endpoint || `/posts/${card.dataset.postId}`;

                    const payload = {
                        hub_type: card.dataset.hubType || null,
                        hub_id: card.dataset.hubId || null,
                        parent_id: card.dataset.parentId || null,
                        body: el(config.selectors.textarea)?.value || '',
                        media_ids: mediaIds,
                        is_spoiler: el(config.selectors.spoiler)?.checked ? 1 : 0,
                        is_locked: el(config.selectors.lock)?.checked ? 1 : 0,
                        ...config.extraPayload?.(card)
                    };

                    const res = await fetch(endpoint, {
                        method: config.method,
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                        body: JSON.stringify(payload)
                    });

                    if (res.ok) window.location.reload();
                    else throw new Error((await res.json())?.message || 'Request failed');
                } catch (err) {
                    console.error(err);
                    alert('Error: ' + err.message);
                    btn.disabled = false;
                    if (el(config.selectors.spinner)) el(config.selectors.spinner).classList.add('d-none');
                }
            }
        });
    });
});
</script>
@endonce