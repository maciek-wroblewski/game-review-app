@props(['headtitle' => 'GameHub'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <title>{{ $headtitle }} | VGDB</title>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>

<body class="bg-light d-flex flex-column min-vh-100">

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">

        <div class="container">

            <!-- Logo -->
            <a class="navbar-brand fw-bold fs-4" href="/">
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

            <div class="collapse navbar-collapse" id="navbarNav">

                <!-- Left Side -->
                <ul class="navbar-nav me-auto">

                    <li class="nav-item">
                        <a class="nav-link" href="/">
                            Home
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/games">
                            Games
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" href="/hub">
                            Community
                        </a>
                    </li>

                </ul>

                <!-- Right Side -->
                <ul class="navbar-nav align-items-lg-center gap-lg-2">

                    @auth

                        <!-- Notifications -->
                        <li class="nav-item dropdown">

                            <a class="nav-link position-relative"
                               href="#"
                               role="button"
                               data-bs-toggle="dropdown"
                               aria-expanded="false">

                                <i class="bi bi-bell-fill fs-5"></i>

                                @if(auth()->user()->notifications()->where('read', false)->count() > 0)

                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                                        {{ auth()->user()->notifications()->where('read', false)->count() }}

                                    </span>

                                @endif

                            </a>

                            <ul class="dropdown-menu dropdown-menu-end shadow border-0 p-2"
                                style="width: 340px; max-height: 500px; overflow-y: auto;">

                                <li class="px-3 py-2 border-bottom mb-2">

                                    <h6 class="fw-bold mb-0">
                                        Notifications
                                    </h6>

                                </li>

                                @forelse(auth()->user()->notifications->take(10) as $notification)

                                    <li>

                                        <form action="/notifications/{{ $notification->id }}/read"
                                              method="POST">

                                            @csrf

                                            <button type="submit"
                                                    class="dropdown-item rounded-3 py-3
                                                           {{ !$notification->read ? 'bg-light' : '' }}">

                                                <div class="d-flex align-items-start gap-3">

                                                    <!-- Avatar -->
                                                    <div class="rounded-circle bg-primary text-white
                                                                d-flex align-items-center justify-content-center"
                                                         style="
                                                            width: 42px;
                                                            height: 42px;
                                                            flex-shrink: 0;
                                                            font-size: 1rem;
                                                         ">

                                                        {{ strtoupper(substr($notification->fromUser->username ?? 'S', 0, 1)) }}

                                                    </div>

                                                    <!-- Content -->
                                                    <div class="flex-grow-1">

                                                        <div class="small text-wrap">

                                                            {{ $notification->message }}

                                                        </div>

                                                        <small class="text-muted">

                                                            {{ $notification->created_at->diffForHumans() }}

                                                        </small>

                                                    </div>

                                                </div>

                                            </button>

                                        </form>

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