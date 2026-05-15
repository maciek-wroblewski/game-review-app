@props(['post'])

<div class="card-header clickable-card bg-white d-flex justify-content-between align-items-center py-3 border-bottom-0"
    data-href="/posts/{{ $post->id }}" style="cursor: pointer;">
    <div class="d-flex align-items-center">
        <span class="user-popover-wrapper position-relative">
            <!-- The Trigger -->
            <a href="/users/{{ $post->author->username ?? '#' }}" 
            class="user-card-trigger d-inline-block" 
            data-bs-toggle="popover" 
            data-bs-trigger="manual">
                
                <img src="{{ $post->author->avatar ?? asset('images/default-avatar.png') }}"
                    class="rounded-circle me-3 border" 
                    style="width: 48px; height: 48px; object-fit: cover;" 
                    alt="{{ $post->author->name ?? 'User' }}">
            </a>

            <!-- The Hidden Component Template -->
            <div class="popover-template d-none">
                <x-user-card :user="$post->author" layout="compact" />
            </div>
        </span>
        <div>
            <div class="d-flex align-items-center">
                <a href="/users/{{ $post->author->username ?? '#' }}"
                    class="text-decoration-none fw-bold text-dark fs-5 me-2">
                    {{ $post->author->username ?? 'Anonymous' }}
                </a>
                @if(optional($post->author)->verified) <i class="bi bi-patch-check-fill text-primary"></i> @endif
                <span class="js-editing-badge badge bg-warning text-dark ms-2 d-none">Editing</span>
            </div>
            <div class="text-muted small d-flex flex-wrap gap-2">
                <span>{{ $post->created_at->diffForHumans(null, true, true) }}</span>
                @if($post->created_at->ne($post->updated_at))
                <span class="fst-italic"
                    title="Edited on {{ $post->updated_at->diffForHumans(null, true, true) }}">(Edited)</span>
                @endif
                @if($post->hub)
                @php
                $routeName = $post->hub->getTable();
                $href = '/' . $routeName . "/" . $post->hub_id;
                @endphp
                <span class="text-secondary">&bull;</span>
                <span>Posted in
                    <a href="{{$href}}">
                        {{ $post->hub->title ?? $post->hub->name ?? $post->hub->username ?? 'Hub' }}
                    </a>
                </span>
                @endif
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2">
        @if($post->author)
        <x-follow-button :target-user="$post->author" /> @endif
        @auth
        @if(auth()->id() === $post->user_id && !$post->trashed())
        <div class="dropdown">
            <button class="btn btn-light btn-sm rounded-circle border-0" data-bs-toggle="dropdown"><i
                    class="bi bi-three-dots"></i></button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                <li><button class="js-btn-edit dropdown-item"><i class="bi bi-pencil me-2"></i>Edit</button></li>
                <li><button class="js-btn-delete dropdown-item text-danger"><i
                            class="bi bi-trash me-2"></i>Delete</button></li>
            </ul>
        </div>
        @endif
        @endauth
    </div>
</div>

<style>
    /* 1. Smooth avatar trigger interaction */
    .user-card-trigger img {
        transition: transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), box-shadow 0.25s ease;
    }
    .user-card-trigger:hover img {
        transform: scale(1.06);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
    }

    /* 2. Modern Popover Container Styling */
    .user-card-popover {
        border: none !important;
        background: transparent !important;
        /* Ultra-smooth modern shadow */
        filter: drop-shadow(0 12px 12px rgba(0, 0, 0, 0.01));
    }

    /* Hide the default harsh Bootstrap arrow for a cleaner floating card look */
    .user-card-popover .popover-arrow {
        display: none !important;
    }

    /* 3. The Magic: Animating the inner content safely */
    .user-card-popover .popover-body {
        padding: 0 !important;
        border-radius: 14px;
        overflow: hidden;
        background: #fff;
        
        /* Animation state setup */
        opacity: 0;
        transform: translateY(10px) scale(0.96);
        transition: opacity 0.25s cubic-bezier(0.34, 1.56, 0.64, 1), 
                    transform 0.25s cubic-bezier(0.34, 1.56, 0.64, 1);
    }

    /* Trigger animation when Bootstrap adds the .show class */
    .user-card-popover.show .popover-body {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const triggers = document.querySelectorAll('.user-card-trigger');

        triggers.forEach(trigger => {
            const wrapper = trigger.closest('.user-popover-wrapper');
            const template = wrapper.querySelector('.popover-template');
            let timeout = null;

            const popover = new bootstrap.Popover(trigger, {
                html: true,
                content: template.innerHTML,
                sanitize: false,
                customClass: 'user-card-popover',
                placement: 'auto', 
                
                // Pushed distance to 12px for a modern floating appearance
                offset: [0, 12] 
            });

            const startHideTimeout = () => {
                timeout = setTimeout(() => {
                    popover.hide();
                }, 400); // Dropped down from 1200ms to 400ms for snappier UX
            };

            const clearHideTimeout = () => {
                clearTimeout(timeout);
            };

            // Trigger wrapper hovers
            wrapper.addEventListener('mouseenter', () => {
                clearHideTimeout();
                popover.show();
            });
            wrapper.addEventListener('mouseleave', startHideTimeout);

            // Keep open when hovering inside the actual popover card
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
@once
<script>
    document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('.clickable-card').forEach(card => {
                card.addEventListener('click', (e) => {
                    // If the user clicked a link, a button, or something inside them, ignore it
                    if (e.target.closest('a') || e.target.closest('button') || e.target.closest('.dropdown-menu')) {
                        return;
                    }
                    
                    // Otherwise, redirect to the post
                    const url = card.getAttribute('data-href');
                    if (url) {
                        window.location.href = url;
                    }
                });
            });
        });
        
</script>
@endonce