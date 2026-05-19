@props(['headtitle' => 'GameHub'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta http-equiv="X-UA-Compatible"
          content="ie=edge">

    <title>{{ $headtitle }} | VGDB</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite([
        'resources/sass/app.scss',
        'resources/js/app.js'
    ])

</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Fixed Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm"
         style="
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 9999;
            background: rgba(33, 37, 41, 0.92);
            backdrop-filter: blur(12px);
         ">

        <div class="container">

            <!-- Logo -->
            <a class="navbar-brand fw-bold fs-4"
               href="/">

                <i class="bi bi-controller me-2 text-primary"></i>

                VG<span class="text-primary">DB</span>

            </a>

            <!-- Mobile Button -->
            <button class="navbar-toggler"
                    type="button"
                    data-bs-toggle="collapse"
                    data-bs-target="#navbarNav"
                    aria-controls="navbarNav"
                    aria-expanded="false"
                    aria-label="Toggle navigation">

                <span class="navbar-toggler-icon"></span>

            </button>

            <div class="collapse navbar-collapse"
                 id="navbarNav">

                <!-- Left Side -->
                <ul class="navbar-nav me-auto">

                    <li class="nav-item">

                        <a class="nav-link"
                           href="/">

                            Home

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link"
                           href="/games">

                            Games

                        </a>

                    </li>

                    <li class="nav-item">

                        <a class="nav-link"
                           href="/posts">

                            Community

                        </a>

                    </li>

                    @auth

                        @if(auth()->user()->is_admin)

                            <li class="nav-item">

                                <a class="nav-link text-warning fw-semibold"
                                   href="/admin">

                                    <i class="bi bi-shield-lock-fill me-1"></i>
                                    Admin Panel

                                </a>

                            </li>

                        @endif

                    @endauth

                </ul>

                <!-- Search Bar -->
                <form class="d-flex mx-auto col-12 col-lg-5 mb-3 mb-lg-0"
                      role="search"
                      action="/search"
                      method="GET">

                    <input class="form-control rounded-pill bg-light border-0 px-4"
                           type="search"
                           name="q"
                           placeholder="Search games, users..."
                           aria-label="Search"
                           value="{{ request('q') }}">

                </form>

                <!-- Right Side -->
                <ul class="navbar-nav align-items-lg-center gap-lg-2">

                    @auth

                        @php
                            $unreadNotificationCount = auth()->user()
                                ->notifications()
                                ->where('read', false)
                                ->count();
                        @endphp

                        <!-- Notifications -->
                        <li class="nav-item dropdown">

                            <a class="nav-link position-relative"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">

                                <i class="bi bi-bell-fill fs-5"></i>

                                @if($unreadNotificationCount > 0)

                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                                        {{ $unreadNotificationCount }}

                                    </span>

                                @endif

                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2"
                                style="width: 340px; max-height: 500px; overflow-y: auto;">

                                <!-- Header -->
                                <li class="px-3 py-2 border-bottom mb-2">

                                    <div class="d-flex justify-content-between align-items-center">

                                        <h6 class="fw-bold mb-0">
                                            Notifications
                                        </h6>

                                        @if($unreadNotificationCount > 0)

                                            <form action="/notifications/read-all"
                                                  method="POST">

                                                @csrf

                                                <button type="submit"
                                                        class="btn btn-sm btn-link text-decoration-none p-0">

                                                    Mark all as read

                                                </button>

                                            </form>

                                        @endif

                                    </div>

                                </li>

                                <!-- Notifications List -->
                                @forelse(auth()->user()->notifications->take(10) as $notification)

                                    <li>

                                        <div class="dropdown-item rounded-3 py-3
                                                    {{ !$notification->read ? 'bg-light' : '' }}">

                                            <div class="d-flex align-items-start gap-3">

                                                <!-- Avatar -->
                                                <a href="/users/{{ optional($notification->fromUser)->username }}"
                                                   class="text-decoration-none">

                                                    <div class="rounded-circle bg-primary text-white
                                                                d-flex align-items-center justify-content-center overflow-hidden"
                                                         style="
                                                            width: 42px;
                                                            height: 42px;
                                                            flex-shrink: 0;
                                                            font-size: 1rem;
                                                         ">

                                                        @if(optional($notification->fromUser)->avatar)

                                                            <img src="{{ optional($notification->fromUser)->avatar->file_path }}"
                                                                 alt="Avatar"
                                                                 class="w-100 h-100 object-fit-cover">

                                                        @else

                                                            {{ strtoupper(substr(optional($notification->fromUser)->username ?? 'S', 0, 1)) }}

                                                        @endif

                                                    </div>

                                                </a>

                                                <!-- Content -->
                                                <div class="flex-grow-1">

                                                    <div class="small text-wrap mb-1">

                                                        <a href="/users/{{ optional($notification->fromUser)->username }}"
                                                           class="fw-semibold text-decoration-none">

                                                            {{ optional($notification->fromUser)->username }}

                                                        </a>

                                                        <span class="text-dark">

                                                            {{ str_replace(optional($notification->fromUser)->username, '', $notification->message) }}

                                                        </span>

                                                    </div>

                                                    <small class="text-muted">

                                                        {{ $notification->created_at->diffForHumans() }}

                                                    </small>

                                                </div>

                                                <!-- Mark as read -->
                                                @if(!$notification->read)

                                                    <form action="/notifications/{{ $notification->id }}/read"
                                                          method="POST">

                                                        @csrf

                                                        <button type="submit"
                                                                class="btn btn-sm btn-light border">

                                                            <i class="bi bi-check-lg"></i>

                                                        </button>

                                                    </form>

                                                @endif

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

                        <!-- Username -->
                        <li class="nav-item">

                            <a class="nav-link fw-semibold"
                               href="/users/{{ auth()->user()->username }}">

                                <i class="bi bi-person-circle me-1"></i>

                                {{ auth()->user()->username }}

                                @if(auth()->user()->is_admin)

                                    <span class="badge bg-danger ms-1">
                                        ADMIN
                                    </span>

                                @endif

                            </a>

                        </li>

                        <!-- Edit Profile -->
                        <li class="nav-item">

                            <a href="/profile"
                               class="btn btn-outline-light btn-sm">

                                Edit Profile

                            </a>

                        </li>

                        <!-- Logout -->
                        <li class="nav-item">

                            <form method="POST"
                                  action="{{ route('logout') }}">

                                @csrf

                                <button type="submit"
                                        class="btn btn-primary btn-sm">

                                    <i class="bi bi-box-arrow-right me-1"></i>
                                    Log Out

                                </button>

                            </form>

                        </li>

                    @endauth

                    @guest

                        <li class="nav-item">

                            <a class="btn btn-outline-light btn-sm"
                               href="/login">

                                Login

                            </a>

                        </li>

                        <li class="nav-item">

                            <a class="btn btn-primary btn-sm"
                               href="/register">

                                Register

                            </a>

                        </li>

                    @endguest

                </ul>

            </div>

        </div>

    </nav>

    <!-- Navbar Spacer -->
    <div style="height: 90px;"></div>

    <!-- Main Content -->
    <main class="flex-grow-1 py-4">

        {{ $slot }}

    </main>

    <!-- Footer -->
    <footer class="bg-dark text-light text-center py-4 mt-auto">

        <div class="container">

            <p class="mb-0 text-secondary">
                &copy; {{ date('Y') }} VGDB. All rights reserved.
            </p>

        </div>

    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>