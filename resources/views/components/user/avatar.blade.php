@props(['user', 'size' => null])

<div class="user-popover-wrapper position-relative d-inline-block">
    
    {{-- Click to Profile & Hover Popover Trigger --}}
    <span" 
       class="user-card-trigger d-inline-block text-decoration-none" 
       data-bs-toggle="popover" 
       data-bs-trigger="manual">
       
        {{-- Base Avatar --}}
        <x-user.static-avatar :user="$user" :size="$size" layout="compact" />
        
    </span>

    {{-- Popover Content --}}
    <div class="popover-template d-none">
        <x-user.card :user="$user" layout="compact" :interactive="false" />
    </div>
    
</div>

{{-- STYLES FOR HOVER ANIMATION & POPOVER --}}
<style>
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
        filter: drop-shadow(0 12px 12px rgba(0, 0, 0, 0.01));
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
        transition: opacity 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    .user-card-popover.show .popover-body {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
</style>

{{-- POPOVER LOGIC --}}
@once
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.user-card-trigger').forEach(trigger => {
            const wrapper = trigger.closest('.user-popover-wrapper');
            const template = wrapper.querySelector('.popover-template');
            let timeout = null;

            const popover = new bootstrap.Popover(trigger, {
                html: true,
                content: template.innerHTML,
                sanitize: false,
                customClass: 'user-card-popover',
                placement: 'auto', 
                offset: [0, 12] 
            });

            const startHideTimeout = () => { timeout = setTimeout(() => popover.hide(), 400); };
            const clearHideTimeout = () => clearTimeout(timeout);

            wrapper.addEventListener('mouseenter', () => { clearHideTimeout(); popover.show(); });
            wrapper.addEventListener('mouseleave', startHideTimeout);

            trigger.addEventListener('inserted.bs.popover', () => {
                const popoverElement = document.getElementById(trigger.getAttribute('aria-describedby'));
                if (popoverElement) {
                    popoverElement.addEventListener('mouseenter', clearHideTimeout);
                    popoverElement.addEventListener('mouseleave', startHideTimeout);
                }
            });
        });
    });
</script>
@endonce