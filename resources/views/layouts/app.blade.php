<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>

    <meta charset="utf-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1">

    <meta name="csrf-token"
          content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect"
          href="https://fonts.bunny.net">

    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap"
          rel="stylesheet" />

    <!-- Scripts -->
    @vite([
        'resources/sass/app.scss',
        'resources/js/app.js',
        'resources/js/bootstrap.js',
        'resources/css/tailwind.css'
    ])

</head>

<body class="font-sans antialiased">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">

        <!-- Fixed Navbar -->>
        <div style="
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            width: 100%;
            z-index: 9999;
            backdrop-filter: blur(12px);
        ">

            @include('layouts.navigation')

        </div>

        <<!-- Spacer for fixed navbar -->>
        <div style="height: 90px;"></div>

        <!-- Page Heading -->
        @isset($header)

            <header class="bg-white dark:bg-gray-800 shadow">

                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">

                    {{ $header }}

                </div>

            </header>

        @endisset

        <!-- Page Content -->
        <main>

            {{ $slot }}

        </main>

    </div>

</body>

</html>