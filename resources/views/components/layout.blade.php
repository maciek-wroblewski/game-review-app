<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>{{ $headtitle }}</title>
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])
</head>
<body>
    <header>
        <nav>
            <ul>
                <li><a href="/">Home</a></li>
                <li><a href="/Games">Games</a></li>
                <li><a href="/Hub">Hub</a></li>
                <li><a href="/Profile">Profile</a></li>
            </ul>
        </nav>
    </header>
    <main>
        {{ $slot }}
    </main>
</body>
</html>