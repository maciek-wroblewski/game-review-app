@props(['user', 'size' => null])

{{-- 1. Static Trigger Wrapper: A solid block to provide an uninterrupted hover zone --}}
<div class="user-popover-wrapper position-relative d-inline-block align-middle">
    
    <span 
       class="user-card-trigger d-block text-decoration-none" 
       data-bs-toggle="popover" 
       data-bs-trigger="manual">
       
        {{-- Base Avatar --}}
        <x-user.static-avatar :user="$user" :size="$size" layout="compact" />
        
    </span>

    {{-- Popover Template Cache --}}
    <div class="popover-template d-none">
        <x-user.card :user="$user" layout="compact" />
    </div>
    
</div>

@once
{{-- STYLES FOR HOVER ANIMATION & POPOVER --}}
<style>
    /* Continuous block surface area preventing microscopic mouseleave events */
    .user-popover-wrapper {
        padding: 2px;
    }

    /* Avatar Hover Animation */
    .user-card-trigger img {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
    }

    .user-card-trigger:hover img {
        transform: scale(1.06);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    /* Popover Styling */
    .user-card-popover {
        border: none !important;
        background: transparent !important;
        filter: drop-shadow(0 12px 24px rgba(0, 0, 0, 0.12));
    }

    .user-card-popover .popover-arrow {
        display: none !important;
    }

    .user-card-popover .popover-body {
        padding: 0 !important;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        opacity: 0;
        transform: translateY(10px) scale(0.96);
        transition: opacity 0.22s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.22s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .user-card-popover.show .popover-body {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
</style>

{{-- DELEGATED POPOVER LOGIC WITH STABLE DIE TIME --}}
<script>
    document.addEventListener('DOMContentLoaded', function () {
        let hideTimeout = null;
        let showTimeout = null;
        let activePopoverInstance = null;
        let activeTrigger = null;

        const clearTimeouts = () => {
            clearTimeout(hideTimeout);
            clearTimeout(showTimeout);
        };
        
        // Die-time duration in milliseconds (Time before card closes after mouse leaves)
        const DIE_TIME = 400; 
        // Activation delay to prevent accidental popups when skimming across the screen
        const SHOW_DELAY = 150;

        // 2. Capture mouse entrance into the unified hover area
        document.addEventListener('mouseenter', function (e) {
            const wrapper = e.target.closest('.user-popover-wrapper');
            if (!wrapper) return;

            clearTimeouts();

            const trigger = wrapper.querySelector('.user-card-trigger');
            if (!trigger) return;

            // If a different popover is running, close it immediately for snappier UI
            if (activeTrigger && activeTrigger !== trigger) {
                if (activePopoverInstance) activePopoverInstance.hide();
            }

            activeTrigger = trigger;

            // Handle delayed creation/showing
            showTimeout = setTimeout(() => {
                let popoverInstance = bootstrap.Popover.getInstance(trigger);
                
                if (!popoverInstance) {
                    const template = wrapper.querySelector('.popover-template');
                    popoverInstance = new bootstrap.Popover(trigger, {
                        html: true,
                        content: template.innerHTML,
                        sanitize: false,
                        customClass: 'user-card-popover',
                        placement: 'auto', 
                        offset: [0, 12] 
                    });

                    // Ensure the popover overlay card itself keeps its parent wrapper alive on hover
                    trigger.addEventListener('inserted.bs.popover', () => {
                        const popoverElement = document.getElementById(trigger.getAttribute('aria-describedby'));
                        if (popoverElement) {
                            popoverElement.addEventListener('mouseenter', clearTimeouts);
                            popoverElement.addEventListener('mouseleave', startHideTimeout);
                        }
                    });
                }

                activePopoverInstance = popoverInstance;
                popoverInstance.show();
            }, SHOW_DELAY);

        }, true);

        const startHideTimeout = () => {
            clearTimeouts();
            hideTimeout = setTimeout(() => {
                if (activePopoverInstance) {
                    activePopoverInstance.hide();
                    activePopoverInstance = null;
                    activeTrigger = null;
                }
            }, DIE_TIME);
        };

        // 3. Capture mouse exit across the DOM landscape
        document.addEventListener('mouseleave', function (e) {
            const wrapper = e.target.closest('.user-popover-wrapper');
            if (wrapper) {
                startHideTimeout();
            }
        }, true);
    });
</script>
@endonce