@props(['headtitle' => 'GameHub'])

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    
    <title>{{ $headtitle }} | VGDB</title>

    <!-- Bootstrap Icons (Optional but recommended for the Star/User icons) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">

    {{-- TODO styles --}}
    <nav>
        <div>
            <a href="/">
                <i class="bi bi-controller me-2"></i>VG<span class="text-primary">DB</span>
            </a>

            <div>
                <ul>
                    <li>
                        <a href="/">Home</a>
                    </li>
                    <li>
                        <a href="/Games">Games</a>
                    </li>
                    <li>
                        <a href="/Hub">Community</a>
                    </li>
                </ul>
                
                <ul>
                    <li>
                        <a href="/Profile">
                            <i class="bi bi-person-circle me-1"></i> Profile
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content Area -->
    <main>
        {{ $slot }}
    </main>

    <footer>
        &copy; {{ date('Y') }} VGDB. All rights reserved.
    </footer>

</body>
</html>