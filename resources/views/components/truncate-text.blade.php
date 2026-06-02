@props(['size' => 2])

<div class="truncate-wrapper" data-js="truncate-text" style="--lines: {{ $size }};">
    <div class="truncate-content">
        {{ $slot }}
    </div>
    <button type="button" class="btn btn-link p-0 mt-1 truncate-btn d-none">{{ __('common.read_more') }}</button>
</div>

@once
<style>
    .truncate-wrapper .truncate-content {
        display: -webkit-box;
        -webkit-box-orient: vertical;
        -webkit-line-clamp: var(--lines, 2);
        overflow: hidden;
        transition: max-height 0.35s ease-in-out;
    }

    /* Keep display block during expansion/collapsing to allow smooth height transitions */
    .truncate-wrapper.is-expanded .truncate-content {
        display: block;
        -webkit-line-clamp: unset;
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        const wrappers = document.querySelectorAll('[data-js="truncate-text"]');

        wrappers.forEach(wrapper => {
            const content = wrapper.querySelector('.truncate-content');
            const btn = wrapper.querySelector('.truncate-btn');

            if (!content || !btn) return;

            // 1. Only show the "Read more" button if the text actually overflows the line limit
            if (content.scrollHeight > content.clientHeight) {
                btn.classList.remove('d-none');
                // Cache the initial collapsed height
                wrapper.dataset.collapsedHeight = content.clientHeight;
            }

            btn.addEventListener('click', () => {
                const isExpanded = wrapper.classList.contains('is-expanded');
                const collapsedHeight = parseInt(wrapper.dataset.collapsedHeight, 10);

                if (!isExpanded) {
                    // --- EXPANDING ---
                    const fullHeight = content.scrollHeight;

                    // Set explicit height to current collapsed height, then swap layout class
                    content.style.maxHeight = collapsedHeight + 'px';
                    wrapper.classList.add('is-expanded');

                    // Force a DOM reflow to trigger the transition
                    content.offsetHeight;

                    // Animate to the full height
                    content.style.maxHeight = fullHeight + 'px';
                    btn.textContent = '{{ __('common.read_less') }}';

                    // Clean up maxHeight styling after transition finishes for responsiveness
                    const onTransitionEnd = (e) => {
                        if (e.propertyName === 'max-height') {
                            content.style.maxHeight = 'none';
                            content.removeEventListener('transitionend', onTransitionEnd);
                        }
                    };
                    content.addEventListener('transitionend', onTransitionEnd);

                } else {
                    // --- COLLAPSING ---
                    const fullHeight = content.scrollHeight;

                    // Revert from 'none' to explicit full height to start the transition
                    content.style.maxHeight = fullHeight + 'px';
                    content.offsetHeight; // Force reflow

                    // Animate down to the cached collapsed height
                    content.style.maxHeight = collapsedHeight + 'px';
                    btn.textContent = '{{ __('common.read_more') }}';

                    const onTransitionEnd = (e) => {
                        if (e.propertyName === 'max-height') {
                            wrapper.classList.remove('is-expanded');
                            content.style.maxHeight = ''; // Reset inline style
                            content.removeEventListener('transitionend', onTransitionEnd);
                        }
                    };
                    content.addEventListener('transitionend', onTransitionEnd);
                }
            });
        });
    });
</script>
@endonce