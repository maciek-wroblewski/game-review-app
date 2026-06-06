export default class PostFormActions {
    constructor() {
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

                        if (res.ok) {
                            const data = await res.json();
                            
                            // Clean up button/spinner state
                            btn.disabled = false;
                            if (el(config.selectors.spinner)) el(config.selectors.spinner).classList.add('d-none');

                            if (config.method === 'PUT') {
                                if (data.html) {
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(data.html, 'text/html');
                                    const newCard = doc.body.firstElementChild;
                                    if (newCard) {
                                        card.replaceWith(newCard);
                                        return;
                                    }
                                }
                            } else if (config.method === 'POST') {
                                if (data.html) {
                                    const parser = new DOMParser();
                                    const doc = parser.parseFromString(data.html, 'text/html');
                                    const newEl = doc.body.firstElementChild;

                                    if (newEl) {
                                        // Reset form values
                                        if (el(config.selectors.textarea)) el(config.selectors.textarea).value = '';
                                        if (el(config.selectors.spoiler)) el(config.selectors.spoiler).checked = false;
                                        if (el(config.selectors.lock)) el(config.selectors.lock).checked = false;
                                        if (config.resetMeter) config.resetMeter(card);

                                        // Reset media uploader if it exists
                                        const uploader = card.querySelector('.js-media-uploader');
                                        if (uploader) {
                                            const gallery = uploader.querySelector('.js-uploader-gallery');
                                            const uploaderContainer = uploader.querySelector('.js-uploader-gallery-container');
                                            const fileInput = uploader.querySelector('.js-upload-input');
                                            if (gallery) {
                                                gallery.innerHTML = '';
                                                gallery.classList.add('d-none');
                                            }
                                            if (uploaderContainer) {
                                                uploaderContainer.classList.add('is-empty');
                                            }
                                            if (fileInput) {
                                                fileInput.value = '';
                                            }
                                        }

                                        const parentId = card.dataset.parentId;
                                        if (parentId) {
                                            // Comment/reply
                                            const parentCard = document.querySelector(`.js-post-card[data-post-id="${parentId}"]`);
                                            if (parentCard) {
                                                const repliesContainer = parentCard.querySelector('.js-comment-list-container');
                                                const repliesContent = parentCard.querySelector('.js-replies-content') || document.querySelector('.js-replies-content');

                                                if (repliesContent) {
                                                    // Close the reply input container
                                                    const replyInputWrapper = card.closest('.js-reply-container');
                                                    if (replyInputWrapper) {
                                                        replyInputWrapper.style.maxHeight = '0';
                                                        replyInputWrapper.style.opacity = '0';
                                                        replyInputWrapper.dataset.open = 'false';
                                                    }

                                                    // Ensure the toggle button is active (it might have been disabled if there were 0 replies)
                                                    const repliesContainerWrapper = parentCard.querySelector('.js-replies-container');
                                                    if (repliesContainerWrapper) {
                                                        const disabledBtn = repliesContainerWrapper.querySelector('button[disabled]');
                                                        if (disabledBtn) {
                                                            repliesContainerWrapper.innerHTML = `
                                                                <button class="js-btn-show-replies btn btn-sm btn-light rounded-pill border-0 small"
                                                                    data-post-id="${parentId}">
                                                                    <i class="bi bi-chevron-down me-1"></i>
                                                                    <span class="btn-text">Show Replies</span>
                                                                </button>
                                                            `;
                                                        }
                                                    }

                                                    const toggleBtn = parentCard.querySelector('.js-btn-show-replies');

                                                    // Hide empty message on show page if any
                                                    const emptyMsg = document.querySelector('.js-replies-empty-msg');
                                                    if (emptyMsg) {
                                                        emptyMsg.remove();
                                                    }
                                                    const countTitle = document.querySelector('.js-replies-count-title');
                                                    if (countTitle) {
                                                        countTitle.classList.remove('d-none');
                                                    }

                                                    if (repliesContainer && repliesContainer.dataset.loaded !== 'true') {
                                                        // Toggle replies open to trigger the dynamic fetch
                                                        repliesContainer.dispatchEvent(new CustomEvent('toggle-replies', {
                                                            bubbles: true,
                                                            detail: { btn: toggleBtn }
                                                        }));
                                                    } else {
                                                        // Already loaded, so slide in the new reply at the top
                                                        const placeholder = repliesContent.querySelector('.text-placeholder');
                                                        if (placeholder) {
                                                            placeholder.remove();
                                                        }
                                                        const oldPlaceholder = repliesContent.querySelector('.text-center');
                                                        if (oldPlaceholder && (oldPlaceholder.textContent.includes('replies') || oldPlaceholder.textContent.includes('Replies') || oldPlaceholder.classList.contains('text-placeholder'))) {
                                                            oldPlaceholder.remove();
                                                        }

                                                        newEl.style.opacity = '0';
                                                        newEl.style.maxHeight = '0px';
                                                        newEl.style.overflow = 'hidden';
                                                        newEl.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';

                                                        repliesContent.insertBefore(newEl, repliesContent.firstChild);
                                                        newEl.offsetHeight; // force reflow
                                                        newEl.style.maxHeight = '1000px';
                                                        newEl.style.opacity = '1';

                                                        newEl.addEventListener('transitionend', function handler(ev) {
                                                            if (ev.target !== newEl) return;
                                                            newEl.style.maxHeight = '';
                                                            newEl.style.overflow = '';
                                                            newEl.removeEventListener('transitionend', handler);
                                                        });

                                                        // If closed, expand the replies panel
                                                        if (repliesContainer && repliesContainer.dataset.open !== 'true') {
                                                            repliesContainer.style.maxHeight = '60vh';
                                                            repliesContainer.style.opacity = '1';
                                                            repliesContainer.dataset.open = 'true';

                                                            if (toggleBtn) {
                                                                const btnText = toggleBtn.querySelector('.btn-text');
                                                                if (btnText) btnText.textContent = 'Hide Replies';
                                                                const icon = toggleBtn.querySelector('i');
                                                                if (icon) icon.classList.replace('bi-chevron-down', 'bi-chevron-up');
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else if (card.dataset.reviewType === 'recommendation') {
                                            // Game Review
                                            newEl.style.opacity = '0';
                                            newEl.style.maxHeight = '0px';
                                            newEl.style.overflow = 'hidden';
                                            newEl.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';

                                            card.replaceWith(newEl);
                                            newEl.offsetHeight; // force reflow
                                            newEl.style.maxHeight = '1000px';
                                            newEl.style.opacity = '1';

                                            newEl.addEventListener('transitionend', function handler(ev) {
                                                if (ev.target !== newEl) return;
                                                newEl.style.maxHeight = '';
                                                newEl.style.overflow = '';
                                                newEl.removeEventListener('transitionend', handler);
                                            });

                                            // Hide/remove empty placeholders on the page since there's now a review
                                            const placeholders = document.querySelectorAll('.text-placeholder');
                                            placeholders.forEach(el => el.remove());
                                        } else {
                                            // Top-level post
                                            let container = null;
                                            if (card.dataset.hubType && card.dataset.hubId) {
                                                container = document.getElementById(`hub-posts-feed-${card.dataset.hubType}-${card.dataset.hubId}`);
                                            }
                                            if (!container) {
                                                container = document.getElementById('post-container');
                                            }
                                            if (!container) {
                                                container = document.getElementById('user-posts-container');
                                            }
                                            if (!container) {
                                                container = document.querySelector('.post-list-wrapper [id]');
                                            }

                                            if (container) {
                                                const placeholder = container.querySelector('.text-placeholder');
                                                if (placeholder) {
                                                    placeholder.remove();
                                                }

                                                newEl.style.opacity = '0';
                                                newEl.style.maxHeight = '0px';
                                                newEl.style.overflow = 'hidden';
                                                newEl.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';

                                                container.insertBefore(newEl, container.firstChild);
                                                newEl.offsetHeight; // force reflow
                                                newEl.style.maxHeight = '1000px';
                                                newEl.style.opacity = '1';

                                                newEl.addEventListener('transitionend', function handler(ev) {
                                                    if (ev.target !== newEl) return;
                                                    newEl.style.maxHeight = '';
                                                    newEl.style.overflow = '';
                                                    newEl.removeEventListener('transitionend', handler);
                                                });
                                            }
                                        }
                                        return;
                                    }
                                }
                            }
                            window.location.reload();
                        } else {
                            throw new Error((await res.json())?.message || 'Request failed');
                        }
                    } catch (err) {
                        console.error(err);
                        alert('Error: ' + err.message);
                        btn.disabled = false;
                        if (el(config.selectors.spinner)) el(config.selectors.spinner).classList.add('d-none');
                    }
                }
            });
        });

        // 3. Document-level listener to intercept post deletion
        document.addEventListener('submit', async (e) => {
            const form = e.target;
            const action = form.getAttribute('action');
            if (!action || !action.match(/\/posts\/\d+/)) return;

            const methodInput = form.querySelector('input[name="_method"]');
            const method = methodInput ? methodInput.value : form.getAttribute('method');

            if (method && method.toUpperCase() === 'DELETE') {
                e.preventDefault();

                const csrfToken = form.querySelector('input[name="_token"]')?.value || document.querySelector('meta[name="csrf-token"]')?.content;
                const postIdMatch = action.match(/\/posts\/(\d+)/);
                if (!postIdMatch) return;
                const postId = postIdMatch[1];

                try {
                    const res = await fetch(action, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (res.ok) {
                        const data = await res.json();
                        const isPostShowView = window.location.pathname.replace(/\/$/, '') === `/posts/${postId}`;
                        if (isPostShowView) {
                            // Redirect to home/feed since the main page item was deleted
                            window.location.href = '/';
                        } else {
                            // Smoothly fade out and collapse all instances of the deleted post on the page
                            const wrappers = document.querySelectorAll(`.js-post-card[data-post-id="${postId}"]`);
                            wrappers.forEach(wrapper => {
                                // 1. Establish current height as the inline maxHeight so transition can animate from it
                                const currentHeight = wrapper.offsetHeight;
                                wrapper.style.maxHeight = currentHeight + 'px';
                                wrapper.style.overflow = 'hidden';
                                
                                // Force layout reflow so the browser registers the starting height
                                wrapper.offsetHeight;

                                // 2. Apply transition and animate all properties (height, margins, paddings, scale, opacity) simultaneously
                                wrapper.style.transition = 'max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1), opacity 0.4s cubic-bezier(0.4, 0, 0.2, 1), transform 0.4s cubic-bezier(0.4, 0, 0.2, 1), margin 0.4s cubic-bezier(0.4, 0, 0.2, 1), padding 0.4s cubic-bezier(0.4, 0, 0.2, 1)';
                                
                                wrapper.style.opacity = '0';
                                wrapper.style.transform = 'scale(0.95)';
                                wrapper.style.maxHeight = '0px';
                                wrapper.style.setProperty('padding-top', '0px', 'important');
                                wrapper.style.setProperty('padding-bottom', '0px', 'important');
                                wrapper.style.setProperty('margin-top', '0px', 'important');
                                wrapper.style.setProperty('margin-bottom', '0px', 'important');

                                // 3. Remove or replace when the transition completes
                                const removeHandler = (ev) => {
                                    if (ev.target !== wrapper) return;
                                    wrapper.removeEventListener('transitionend', removeHandler);
                                    if (data && data.html) {
                                        const parser = new DOMParser();
                                        const doc = parser.parseFromString(data.html, 'text/html');
                                        const newForm = doc.body.firstElementChild;
                                        if (newForm) {
                                            newForm.style.opacity = '0';
                                            newForm.style.transition = 'opacity 0.3s ease-in-out';
                                            wrapper.replaceWith(newForm);
                                            newForm.offsetHeight; // force reflow
                                            newForm.style.opacity = '1';
                                        }
                                    } else {
                                        wrapper.remove();
                                    }
                                };
                                wrapper.addEventListener('transitionend', removeHandler);
                            });
                        }
                    } else {
                        throw new Error((await res.json())?.message || 'Delete failed');
                    }
                } catch (err) {
                    console.error(err);
                    alert('Error deleting post: ' + err.message);
                }
            }
        });
    }
}
