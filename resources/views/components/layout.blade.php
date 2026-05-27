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
        /* --- NAWIGACJA I KOMPONENTY GLOBALNE --- */
        .premium-navbar {
            background: rgba(24, 26, 31, 0.82);
            backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.18);
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
            background: rgba(255, 255, 255, 0.08) !important;
            border: 1px solid rgba(255, 255, 255, 0.08) !important;
            color: white !important;
            transition: all 0.2s ease;
        }

        .premium-search::placeholder {
            color: rgba(255, 255, 255, 0.55);
        }

        .premium-search:focus {
            background: rgba(255, 255, 255, 0.12) !important;
            border-color: rgba(13, 110, 253, 0.5) !important;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15) !important;
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
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
        }

        /* --- PROFIL I BADGE --- */
        .profile-pill {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 999px;
            padding: 0.45rem 0.9rem;
        }

        .admin-badge {
            background: linear-gradient(135deg, #ff4d4d, #990000);
            font-size: 0.68rem;
            font-weight: 700;
            letter-spacing: 0.4px;
            padding: 0.35rem 0.55rem;
            border-radius: 999px;
            box-shadow: 0 0 14px rgba(255, 77, 77, 0.32);
        }

        /* --- MOBILE SPECIFIC FIXES --- */
        @media (max-width: 991.98px) {
            .mobile-menu-wrapper {
                background: #181a1f;
                padding: 1rem;
                border-radius: 12px;
                margin-top: 0.5rem;
                box-shadow: 0 10px 25px rgba(0, 0, 0, 0.5);
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

            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse"
                data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto align-items-lg-center gap-lg-2">
                    <li class="nav-item"><a class="nav-link" href="/">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="/games">Games</a></li>
                    <li class="nav-item"><a class="nav-link" href="/posts">Community</a></li>
                </ul>

                <form class="d-flex mx-auto col-12 col-lg-5 mb-3 mb-lg-0" role="search" action="/search" method="GET">
                    <input class="form-control rounded-pill premium-search px-4" type="search" name="q"
                        placeholder="Search games, users..." value="{{ request('q') }}">
                </form>

                <ul class="navbar-nav align-items-lg-center gap-lg-3">

                    @auth
                    <x-notifications />

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
                        <a href="/profile" class="btn btn-outline-light premium-button">Edit Profile</a>
                    </li>

                    <li class="nav-item">
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="btn btn-primary premium-button px-4 w-100">
                                <i class="bi bi-box-arrow-right me-2"></i> Log Out
                            </button>
                        </form>
                    </li>
                    @endauth

                    @guest
                    <li class="nav-item">
                        <a class="btn btn-outline-light premium-button w-100" href="/login">Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-primary premium-button w-100" href="/register">Register</a>
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
                &copy; {{ date('Y') }} VGDB. {{ __('All Rights Reserved') }}.
            </p>
            <div>
                <a href="{{ route('lang.switch', 'en') }}" class="text-secondary text-decoration-none">EN</a> |
                <a href="{{ route('lang.switch', 'pl') }}" class="text-secondary text-decoration-none">PL</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const navBar = document.getElementById('navbarNav');
            const toggler = document.querySelector('.navbar-toggler');
            const notificationToggle = document.getElementById('notificationToggle');
            const overlay = document.getElementById('notificationOverlay');

            // 1. Zamykanie menu po kliknięciu poza nim
            document.addEventListener('click', function (event) {
                const isClickInside = navBar.contains(event.target) || toggler.contains(event.target);
                if (!isClickInside && navBar.classList.contains('show')) {
                    toggler.click();
                }
            });

            // 2. Zamykanie menu po kliknięciu w standardowy link
            const navLinks = document.querySelectorAll('.nav-link:not([data-bs-toggle="dropdown"])');

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