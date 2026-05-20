@props(['headtitle' => 'GameHub'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>{{ $headtitle }} | VGDB</title>
    <link rel="icon" type="image/png" href="{{ asset('vgdb-favicon.png') }}">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite([
        'resources/sass/app.scss',
        'resources/js/app.js'
    ])

    <style>
        .premium-navbar {
            background: rgba(24, 26, 31, 0.82);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255,255,255,0.06);
            box-shadow: 0 4px 24px rgba(0,0,0,0.18);
        }

        .navbar-brand {
            letter-spacing: -0.5px;
        }

        .nav-link {
            transition: color 0.2s ease, opacity 0.2s ease, transform 0.2s ease;
            opacity: 0.88;
        }

        .nav-link:hover {
            opacity: 1;
            transform: translateY(-1px);
        }

        .premium-search {
            background: rgba(255,255,255,0.08) !important;
            border: 1px solid rgba(255,255,255,0.08) !important;
            color: white !important;
            transition: all 0.2s ease;
        }

        .premium-search::placeholder {
            color: rgba(255,255,255,0.55);
        }

        .premium-search:focus {
            background: rgba(255,255,255,0.12) !important;
            border-color: rgba(13,110,253,0.5) !important;
            box-shadow: 0 0 0 0.2rem rgba(13,110,253,0.15) !important;
        }

        .admin-badge {
            background: linear-gradient(135deg, #ff4d4d, #990000);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            box-shadow: 0 0 14px rgba(255,77,77,0.32);
        }

        .admin-link {
            color: #ffd54d !important;
            font-weight: 700;
            text-shadow: 0 0 10px rgba(255,213,77,0.18);
        }

        .admin-link:hover {
            color: #ffe082 !important;
        }

        .profile-pill {
            background: rgba(255,255,255,0.06);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
        }

        .notification-badge {
            box-shadow: 0 0 12px rgba(220,53,69,0.45);
        }

        .premium-button {
            min-width: 120px;
            height: 46px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 999px;
            font-weight: 600;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .premium-button:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
        }

        .notification-dropdown {
            width: 340px;
            max-height: 500px;
            overflow-y: auto;
            border-radius: 18px;
        }

        /* --- MOBILE SPECIFIC FIXES --- */
       /* --- MOBILE SPECIFIC FIXES --- */
        @media (max-width: 991.98px) {
            .mobile-menu-wrapper {
                /* Applied to an INNER wrapper to fix the Bootstrap snapping bug */
                background: #181a1f; 
                padding: 1rem;
                border-radius: 12px;
                margin-top: 0.5rem;
                box-shadow: 0 10px 25px rgba(0,0,0,0.5);
            }
            
            .notification-dropdown {
                width: 100% !important; 
                border: 1px solid rgba(255,255,255,0.1);
                background: rgba(255,255,255,0.05);
            }

            .premium-button {
                width: 100%; 
                margin-top: 0.5rem;
            }

            .nav-item {
                margin-bottom: 0.5rem;
            }
        }
    </style>

</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <nav class="navbar navbar-expand-lg navbar-dark premium-navbar"
         style="position: fixed; top: 0; left: 0; right: 0; width: 100%; z-index: 9999;">

        <div class="container-fluid px-4 px-xl-5">

            <a class="navbar-brand fw-bold fs-3 d-flex align-items-center" href="/">
                <i class="bi bi-controller me-2 text-primary"></i>
                VG<span class="text-primary">DB</span>
            </a>

            <button class="navbar-toggler border-0 shadow-none"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">

                <ul class="navbar-nav me-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item">
                        <a class="nav-link" href="/">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/games">Games</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/posts">Community</a>
                    </li>
                </ul>

                <form class="d-flex mx-auto col-12 col-lg-5 mb-3 mb-lg-0"
                      role="search"
                      action="/search"
                      method="GET">
                    <input class="form-control rounded-pill premium-search px-4"
                           type="search"
                           name="q"
                           placeholder="Search games, users..."
                           value="{{ request('q') }}">
                </form>

                <ul class="navbar-nav align-items-lg-center gap-lg-3">

                    @auth

                        @php
                            $unreadNotificationCount = auth()->user()
                                ->notifications()
                                ->where('read', false)
                                ->count();
                        @endphp

                        <li class="nav-item dropdown">
                            <a class="nav-link position-relative d-inline-block"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown">
                                <i class="bi bi-bell-fill fs-5"></i>
                                @if($unreadNotificationCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge">
                                        {{ $unreadNotificationCount }}
                                    </span>
                                @endif
                                <span class="d-lg-none ms-2">Notifications</span>
                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2 notification-dropdown">
                                <li class="px-3 py-2 border-bottom mb-2">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <h6 class="fw-bold mb-0 {{ request()->is('*') ? 'text-dark' : '' }}">
                                            Notifications
                                        </h6>
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

                                @forelse(auth()->user()->notifications->take(10) as $notification)
                                    <li>
                                        <div class="dropdown-item rounded-4 py-3 {{ !$notification->read ? 'bg-light' : '' }}">
                                            <div class="d-flex align-items-start gap-3">
                                                <a href="/users/{{ optional($notification->fromUser)->username }}" class="text-decoration-none">
                                                    <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center overflow-hidden"
                                                         style="width: 42px; height: 42px; flex-shrink: 0;">
                                                        @if(optional($notification->fromUser)->avatar)
                                                            <img src="{{ optional($notification->fromUser)->avatar->file_path }}" class="w-100 h-100 object-fit-cover">
                                                        @else
                                                            {{ strtoupper(substr(optional($notification->fromUser)->username ?? 'S', 0, 1)) }}
                                                        @endif
                                                    </div>
                                                </a>
                                                <div class="flex-grow-1 text-wrap">
                                                    <div class="small mb-1">
                                                        <a href="/users/{{ optional($notification->fromUser)->username }}" class="fw-semibold text-decoration-none">
                                                            {{ optional($notification->fromUser)->username }}
                                                        </a>
                                                        {{ str_replace(optional($notification->fromUser)->username, '', $notification->message) }}
                                                    </div>
                                                    <small class="text-muted">
                                                        {{ $notification->created_at->diffForHumans() }}
                                                    </small>
                                                </div>
                                            </div>
                                        </div>
                                    </li>
                                @empty
                                    <li class="px-3 py-4 text-center text-muted">
                                        No notifications yet
                                    </li>
                                @endforelse
                            </ul>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link profile-pill d-flex align-items-center gap-2 justify-content-center justify-content-lg-start"
                               href="/users/{{ auth()->user()->username }}">
                                <i class="bi bi-person-circle"></i>
                                <span>{{ auth()->user()->username }}</span>
                                @if(auth()->user()->is_admin)
                                    <span class="admin-badge">ADMIN</span>
                                @endif
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="/profile" class="btn btn-outline-light premium-button">
                                Edit Profile
                            </a>
                        </li>

                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-primary premium-button px-4">
                                    <i class="bi bi-box-arrow-right me-2"></i>
                                    Log Out
                                </button>
                            </form>
                        </li>

                    @endauth

                    @guest
                        <li class="nav-item">
                            <a class="btn btn-outline-light premium-button" href="/login">
                                Login
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="btn btn-primary premium-button" href="/register">
                                Register
                            </a>
                        </li>
                    @endguest

                </ul>

            </div>
        </div>
    </nav>

    <div style="height: 90px;"></div>

    <main class="flex-grow-1 py-4">
        {{ $slot }}
    </main>

    <footer class="bg-dark text-light text-center py-4 mt-auto">
        <div class="container">
            <p class="mb-0 text-secondary">
                &copy; {{ date('Y') }} VGDB. All rights reserved.
            </p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navBar = document.getElementById('navbarNav');
            const toggler = document.querySelector('.navbar-toggler');

            // Close menu when clicking outside
            document.addEventListener('click', function (event) {
                const isClickInside = navBar.contains(event.target) || toggler.contains(event.target);
                if (!isClickInside && navBar.classList.contains('show')) {
                    toggler.click();
                }
            });

            // Close menu when a standard nav link is clicked
            const navLinks = document.querySelectorAll('.nav-link:not(.dropdown-toggle)');
            navLinks.forEach(link => {
                link.addEventListener('click', () => {
                    if (navBar.classList.contains('show')) {
                        toggler.click();
                    }
                });
            });
        });
    </script>

</body>
</html>