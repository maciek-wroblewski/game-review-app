{{-- Data provided by NotificationComposer: $unreadNotificationCount, $recentNotifications --}}

<li class="nav-item dropdown notification-wrapper">
    
    <div id="notificationOverlay" class="notification-overlay d-lg-none"></div>

    <a id="notificationToggle" class="nav-link d-inline-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" data-bs-display="static" aria-expanded="false">
        <i class="bi bi-bell-fill fs-5"></i>
        @if($unreadNotificationCount > 0)
            <span class="small fw-semibold text-white">
                {{ $unreadNotificationCount }}
            </span>
        @endif
        <span class="d-lg-none ms-2">{{ __('notifications.notifications') }}</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 notification-dropdown">
        <li class="px-3 py-2 border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">{{ __('notifications.notifications') }}</h6>
                @if($unreadNotificationCount > 0)
                    <form action="/notifications/read-all" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0 text-end">
                            {{ __('notifications.mark_all_read') }}
                        </button>
                    </form>
                @endif
            </div>
        </li>

        {{-- SCROLLABLE BODY --}}
        <div class="notification-scroll-area">
            <div class="js-notifications-target">
                @if($recentNotifications->isEmpty())
                    <li class="px-3 py-4 text-center text-muted">
                        {{ __('notifications.no_notifications') }}
                    </li>
                @else
                    @include('components.notification-items', ['notifications' => $recentNotifications])
                @endif
            </div>

            @if($recentNotifications->hasMorePages())
                <li class="px-2 mt-2 text-center pb-1">
                    <x-load-more 
                        :paginator="$recentNotifications" 
                        target=".js-notifications-target" 
                        buttonClass="btn btn-sm btn-light text-primary fw-semibold w-100 rounded-pill" 
                        text="{{ __('notifications.load_older') }}" 
                    />
                </li>
            @endif
        </div>
    </ul>
    
    <style>
        .notification-dropdown {
            width: 340px;
            border-radius: 18px;
            
            display: block !important;
            position: absolute !important;
            top: 100% !important; 
            right: 0 !important;
            left: auto !important;
            margin: 0 !important; 
            margin-top: 10px !important;
            
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px) !important;
            
            transition: opacity 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        transform 0.25s cubic-bezier(0.4, 0, 0.2, 1), 
                        visibility 0.25s ease;
            will-change: transform, opacity;
            pointer-events: none;
        }

        .notification-scroll-area {
            max-height: 400px;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 4px; 
        }

        .notification-scroll-area::-webkit-scrollbar {
            width: 6px;
        }
        .notification-scroll-area::-webkit-scrollbar-track {
            background: transparent;
        }
        .notification-scroll-area::-webkit-scrollbar-thumb {
            background-color: rgba(0,0,0,0.15);
            border-radius: 10px;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0) !important;
            pointer-events: auto;
        }

        .notification-overlay {
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(6px);
            z-index: 10000;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.25s ease, visibility 0.25s ease;
        }

        .notification-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        /* --- MOBILE SPECIFIC OVERRIDES --- */
        @media (max-width: 991.98px) {
            .notification-dropdown {
                position: fixed !important;
                
                /* 1. ANCHOR TOP: Sit right below the navbar */
                top: 75px !important; 
                
                /* 2. ONLY CENTER HORIZONTALLY */
                left: 50% !important;
                transform: translateX(-50%) translateY(-15px) !important; 
                
                width: 92% !important;
                max-width: 400px;
                
                /* 3. FLUID HEIGHT: 100vh minus top offset and bottom padding */
                max-height: calc(100vh - 100px) !important; 
                
                display: flex !important;
                flex-direction: column;
                
                z-index: 10001 !important;
                margin-top: 0 !important;
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: #fff;
            }

            .notification-dropdown.show {
                transform: translateX(-50%) translateY(0) !important;
            }

            .notification-dropdown > li:first-child {
                flex-shrink: 0;
            }

            .notification-scroll-area {
                flex-grow: 1;
                max-height: none; 
            }
        }
    </style>
</li>