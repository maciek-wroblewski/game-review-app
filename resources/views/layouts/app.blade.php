<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'GameHub')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-900 text-gray-100 font-sans antialiased flex flex-col min-h-screen">

    <!-- Shared Nav Tile -->
    <nav class="bg-gray-800 border-b border-gray-700 shadow-md sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center space-x-8">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-500 tracking-wider">GAMEHUB</a>
                    <div class="hidden md:flex space-x-4">
                        <a href="{{ route('home') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700 transition">Home</a>
                        <a href="{{ route('hub.index') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700 transition">Hub</a>
                        <a href="{{ route('search') }}" class="px-3 py-2 rounded-md text-sm font-medium hover:bg-gray-700 transition">Search</a>
                    </div>
                </div>
                <div class="flex items-center space-x-4">
                    <!-- Universal Quick-Add Button -->
                    <button class="bg-indigo-600 hover:bg-indigo-500 px-4 py-2 rounded shadow-lg transition-transform transform hover:scale-105 font-bold text-white shadow-indigo-500/20">
                        + Log Game
                    </button>
                    @auth
                        <div class="relative group py-2">
                            <a href="{{ route('user.show', auth()->user()) }}" class="flex items-center space-x-2 cursor-pointer">
                                <img src="{{ auth()->user()->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode(auth()->user()->name).'&background=random' }}" class="w-8 h-8 rounded-full border border-gray-600 transition-transform transform group-hover:scale-110">
                            </a>
                            <div class="absolute right-0 top-full w-48 hidden group-hover:block z-50">
                                <!-- invisible bridge to keep hover active -->
                                <div class="h-2 w-full"></div>
                                <div class="bg-gray-800 rounded-md shadow-lg py-1 border border-gray-700">
                                    <a href="{{ route('user.show', auth()->user()) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Profile</a>
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="block w-full text-left px-4 py-2 text-sm text-gray-300 hover:bg-gray-700">Log Out</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm bg-gray-700 px-4 py-2 font-bold rounded hover:bg-gray-600 transition text-white">Log In</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <div class="flex-grow flex w-full relative mx-auto">
        <!-- Main Content Area -->
        <main class="flex-grow p-4 md:p-8 w-full md:pr-72">
            @yield('content')
        </main>

        <!-- Dynamic Messaging Tile (Right Sidebar on Desktop) -->
        <aside class="hidden md:flex flex-col w-72 border-l border-gray-800 bg-gray-800/50 backdrop-blur-md fixed right-0 top-16 bottom-0 z-40">
            <div class="p-4 border-b border-gray-700/80">
                <div class="flex justify-between items-center">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider">Messages</h3>
                    <button class="text-gray-400 hover:text-white transition">+</button>
                </div>
            </div>
            
            <div class="flex-1 overflow-y-auto p-4 space-y-2" id="conversations-list">
                @auth
                    <div class="text-center mt-10">
                        <div class="inline-block animate-spin w-5 h-5 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
                    </div>
                @else
                    <div class="text-center mt-10">
                        <p class="text-sm text-gray-500">Log in to see messages.</p>
                    </div>
                @endauth
            </div>
        </aside>
    </div>

    @auth
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('/messages/conversations')
                .then(res => res.json())
                .then(data => {
                    const list = document.getElementById('conversations-list');
                    if(data.length === 0) {
                        list.innerHTML = '<div class="text-center mt-10 text-sm text-gray-500">No active conversations.</div>';
                        return;
                    }
                    list.innerHTML = data.map(conv => `
                        <div class="flex items-center space-x-3 cursor-pointer hover:bg-gray-700/50 p-2 rounded-lg transition-colors group">
                            <div class="relative">
                                <img src="${conv.user.avatar_url}" class="w-10 h-10 rounded-full shadow">
                                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border border-gray-800"></span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-baseline">
                                    <div class="font-medium text-sm text-gray-200 group-hover:text-white">${conv.user.name}</div>
                                    <div class="text-xs text-gray-500">${conv.time}</div>
                                </div>
                                <div class="text-xs text-gray-400 truncate">${conv.last_message}</div>
                            </div>
                        </div>
                    `).join('');
                });
        });
    </script>
    @endauth
</body>
</html>
