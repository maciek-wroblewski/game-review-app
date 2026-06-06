export default class UserAvatar {
    constructor() {
        this.hideTimeout = null;
        this.showTimeout = null;
        this.activePopoverInstance = null;
        this.activeTrigger = null;

        // Constants
        this.DIE_TIME = 400; 
        this.SHOW_DELAY = 150;

        document.addEventListener('mouseenter', this.handleMouseEnter.bind(this), true);
        document.addEventListener('mouseleave', this.handleMouseLeave.bind(this), true);
    }

    clearTimeouts() {
        clearTimeout(this.hideTimeout);
        clearTimeout(this.showTimeout);
    }

    startHideTimeout() {
        this.clearTimeouts();
        this.hideTimeout = setTimeout(() => {
            if (this.activePopoverInstance) {
                this.activePopoverInstance.hide();
                this.activePopoverInstance = null;
                this.activeTrigger = null;
            }
        }, this.DIE_TIME);
    }

    handleMouseEnter(e) {
        const wrapper = e.target.closest('.user-popover-wrapper');
        if (!wrapper) return;

        this.clearTimeouts();

        const trigger = wrapper.querySelector('.user-card-trigger');
        if (!trigger) return;

        // If a different popover is running, close it immediately for snappier UI
        if (this.activeTrigger && this.activeTrigger !== trigger) {
            if (this.activePopoverInstance) {
                this.activePopoverInstance.hide();
            }
        }

        this.activeTrigger = trigger;

        // Handle delayed creation/showing
        this.showTimeout = setTimeout(() => {
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
                        popoverElement.addEventListener('mouseenter', this.clearTimeouts.bind(this));
                        popoverElement.addEventListener('mouseleave', this.startHideTimeout.bind(this));
                    }
                });
            }

            this.activePopoverInstance = popoverInstance;
            popoverInstance.show();
        }, this.SHOW_DELAY);
    }

    handleMouseLeave(e) {
        const wrapper = e.target.closest('.user-popover-wrapper');
        if (wrapper) {
            this.startHideTimeout();
        }
    }
}

