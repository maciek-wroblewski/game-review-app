@php
    $user = auth()->user();
    $unreadNotificationCount = $user->notifications()->where('read', false)->count();

    $recentNotifications = $user->notifications()
        ->with('fromUser.avatar')
        ->latest()
        ->take(10)
        ->get();
@endphp

<li class="nav-item dropdown notification-wrapper">
    
    <div id="notificationOverlay" class="notification-overlay d-lg-none"></div>

    <a id="notificationToggle" class="nav-link d-inline-flex align-items-center gap-2" href="#" role="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false">
        <i class="bi bi-bell-fill fs-5"></i>
        @if($unreadNotificationCount > 0)
            <span class="small fw-semibold text-white">
                {{ $unreadNotificationCount }}
            </span>
        @endif
        <span class="d-lg-none ms-2">Notifications</span>
    </a>

    <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 notification-dropdown">
        <li class="px-3 py-2 border-bottom mb-2">
            <div class="d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0 text-dark">Notifications</h6>
                @if($unreadNotificationCount > 0)
                    <form action="/notifications/read-all" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-link text-decoration-none p-0">
                            Mark all as read
                        </button>
                    </form>
                @endif
            </div>
        </li>

        @forelse($recentNotifications as $notification)
            <li>
                <x-clickable-card href="{{ $notification->target_url }}" class="dropdown-item rounded-4 py-3 {{ !$notification->read ? 'bg-light' : '' }}">
                    <div class="d-flex align-items-start gap-3">
                        <a href="/users/{{ optional($notification->fromUser)->username }}" class="text-decoration-none">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center overflow-hidden" style="width: 42px; height: 42px; flex-shrink: 0;">
                                @php
                                    $avatar = optional($notification->fromUser)->avatar;
                                    $avatarPath = is_string($avatar) ? $avatar : optional($avatar)->file_path;
                                @endphp

                                @if($avatarPath)
                                    <img src="{{ $avatarPath }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    {{ strtoupper(substr(optional($notification->fromUser)->username ?? 'S', 0, 1)) }}
                                @endif
                            </div>
                        </a>
                        <div class="flex-grow-1 text-wrap">
                            <div class="small mb-1 text-dark">
                                <a href="/users/{{ optional($notification->fromUser)->username }}" class="fw-semibold text-decoration-none text-dark">
                                    {{ optional($notification->fromUser)->username }}
                                </a>
                                {{ str_replace(optional($notification->fromUser)->username, '', $notification->message) }}
                            </div>
                            <small class="text-muted">
                                {{ $notification->created_at->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </x-clickable-card>
            </li>
        @empty
            <li class="px-3 py-4 text-center text-muted">
                No notifications yet
            </li>
        @endforelse
    </ul>

    {{-- Component Specific Styles --}}
    <style>
        .notification-dropdown {
            width: 340px;
            max-height: 500px;
            overflow-y: auto;
            border-radius: 18px;
            display: block !important;
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: opacity 0.22s ease, transform 0.22s ease, visibility 0.22s ease;
            pointer-events: none;
            right: 0;
            left: auto !important;
            margin-top: 10px;
        }

        .notification-dropdown.show {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
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

        @media (max-width: 991.98px) {
            .notification-dropdown {
                position: fixed !important;
                top: 50% !important;
                left: 50% !important;
                transform: translate(-50%, -45%) !important;
                width: 92% !important;
                max-width: 400px;
                max-height: 80vh !important;
                z-index: 10001 !important;
                margin: 0 !important;
                border: 1px solid rgba(255, 255, 255, 0.1);
                background: #fff;
            }

            .notification-dropdown.show {
                transform: translate(-50%, -50%) !important;
            }
        }
    </style>

    {{-- Component Specific Scripts --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const notificationToggle = document.getElementById('notificationToggle');
            const overlay = document.getElementById('notificationOverlay');

            if (notificationToggle && overlay) {
                // When Bootstrap dropdown opens
                notificationToggle.addEventListener('show.bs.dropdown', () => {
                    if (window.innerWidth < 992) {
                        overlay.classList.add('show');
                        document.body.style.overflow = 'hidden'; 
                    }
                });

                // When Bootstrap dropdown closes
                notificationToggle.addEventListener('hide.bs.dropdown', () => {
                    overlay.classList.remove('show');
                    document.body.style.overflow = ''; 
                });

                // Close dropdown when clicking the dark mobile overlay
                overlay.addEventListener('click', () => {
                    const bsDropdown = bootstrap.Dropdown.getInstance(notificationToggle);
                    if (bsDropdown) {
                        bsDropdown.hide();
                    }
                });
            }
        });
    </script>
</li>