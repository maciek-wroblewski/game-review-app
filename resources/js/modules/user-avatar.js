export default class UserAvatar {
    constructor() {
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

        // 3. Capture mouse exit across the DOM landscape
        document.addEventListener('mouseleave', function (e) {
            const wrapper = e.target.closest('.user-popover-wrapper');
            if (wrapper) {
                startHideTimeout();
            }
        }, true);
    }
}
