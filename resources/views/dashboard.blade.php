@extends('layouts.app')

@section('title', 'GameHub - Home')

@section('content')
<div class="space-y-8">
    
    <!-- Featured Carousel Tile -->
    <section class="bg-gray-800 rounded-xl overflow-hidden shadow-2xl border border-gray-700 relative group">
        <div class="absolute top-4 left-4 bg-indigo-600 text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wide shadow-lg z-10">Featured</div>
        <div class="flex flex-col md:flex-row h-full md:h-96">
            @if($featuredGames->isNotEmpty())
            <div class="md:w-2/3 relative h-64 md:h-full bg-gray-900 overflow-hidden">
                <!-- Using placeholder if no banner -->
                <img src="{{ $featuredGames[0]->banner_image_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1600&q=80' }}" class="w-full h-full object-cover opacity-80 group-hover:opacity-100 transition-opacity duration-500">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
                <div class="absolute bottom-4 md:bottom-8 left-6 md:left-8 right-6">
                    <h2 class="text-3xl md:text-5xl font-black text-white drop-shadow-md tracking-tight">{{ $featuredGames[0]->title }}</h2>
                    <p class="text-gray-300 mt-2 line-clamp-2 text-sm md:text-lg max-w-2xl">{{ $featuredGames[0]->summary }}</p>
                </div>
            </div>
            <div class="md:w-1/3 p-6 md:p-8 flex flex-col justify-center bg-gray-800 border-t md:border-t-0 md:border-l border-gray-700">
                <div class="mb-6">
                    <div class="text-xs text-gray-400 uppercase tracking-wider mb-1 font-semibold">Match Score</div>
                    <div class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-green-400 to-emerald-500">95%</div>
                </div>
                <div class="flex flex-wrap gap-2 mb-8">
                    @foreach($featuredGames[0]->genres as $genre)
                        <span class="bg-gray-700/50 border border-gray-600 text-gray-300 text-xs px-2.5 py-1 rounded-md font-medium">{{ $genre->name }}</span>
                    @endforeach
                </div>
                <a href="{{ route('game.show', $featuredGames[0]->slug) }}" class="block w-full text-center bg-white text-gray-900 font-bold py-3.5 rounded-lg hover:bg-gray-200 hover:scale-[1.02] transition-all shadow-lg">View Details</a>
            </div>
            @else
            <div class="p-8 text-center w-full text-gray-400">No featured games currently.</div>
            @endif
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Activity Timeline / News Tile (2/3 width) -->
        <section class="lg:col-span-2 space-y-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <span class="bg-indigo-500 w-2 h-6 rounded mr-3"></span>
                    Activity Timeline
                </h3>
                <div class="text-sm text-gray-400 hover:text-white cursor-pointer transition font-medium">Filter</div>
            </div>
            
            <div class="space-y-4">
                @forelse($newsFeed as $post)
                <div class="bg-gray-800/80 rounded-xl p-6 border border-gray-700/60 hover:border-indigo-500/50 hover:bg-gray-800 transition shadow-sm">
                    <div class="flex items-center space-x-3 mb-4">
                        <img src="{{ $post->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->name).'&background=random' }}" class="w-10 h-10 rounded-full border border-gray-600">
                        <div class="text-sm">
                            <span class="font-bold text-white hover:underline cursor-pointer">{{ $post->user->name }}</span>
                            <span class="text-gray-400 mx-1">posted in</span>
                            <a href="{{ route('game.show', $post->game->slug ?? '') }}" class="font-semibold text-indigo-400 hover:text-indigo-300 transition">{{ $post->game->title ?? 'General' }}</a>
                            <div class="text-gray-500 text-xs mt-0.5">{{ $post->created_at->diffForHumans() }}</div>
                        </div>
                    </div>
                    <h4 class="text-xl font-bold text-gray-100 mb-2">{{ $post->title }}</h4>
                    <p class="text-gray-400 text-sm leading-relaxed line-clamp-3 mb-5">{{ $post->body }}</p>
                    <div class="flex items-center space-x-6 text-sm text-gray-500 font-medium border-t border-gray-700/50 pt-4">
                        <form method="POST" action="{{ route('post.upvote', $post) }}">
                            @csrf
                            <button type="submit" class="flex items-center space-x-1.5 hover:text-indigo-400 transition group">
                                <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                <span>{{ $post->upvotes }}</span>
                            </button>
                        </form>
                        <button class="flex items-center space-x-1.5 hover:text-indigo-400 transition group">
                            <svg class="w-5 h-5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                            <span>Reply</span>
                        </button>
                    </div>
                </div>
                @empty
                <div class="bg-gray-800 rounded-xl p-8 border border-gray-700 text-center text-gray-500">
                    No activity yet. Be the first to post!
                </div>
                @endforelse
            </div>
        </section>

        <!-- Trending Tile (1/3 width) -->
        <aside class="space-y-4">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-xl font-bold text-white flex items-center">
                    <span class="bg-red-500 w-2 h-6 rounded mr-3"></span>
                    Trending Now
                </h3>
            </div>
            <div class="bg-gray-800/80 rounded-xl border border-gray-700/60 shadow-md overflow-hidden">
                @forelse($trendingGames as $index => $game)
                <a href="{{ route('game.show', $game->slug) }}" class="flex items-center p-4 border-b border-gray-700/50 hover:bg-gray-700/80 transition group last:border-0">
                    <div class="text-2xl font-black text-gray-600 w-8 group-hover:text-red-400 transition">{{ $index + 1 }}</div>
                    <img src="{{ $game->cover_image_url ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=100&q=80' }}" class="w-12 h-16 object-cover rounded shadow-md group-hover:scale-105 transition-transform">
                    <div class="ml-4 flex-1">
                        <div class="font-bold text-gray-200 group-hover:text-white transition">{{ $game->title }}</div>
                        <div class="text-xs text-gray-500 mt-1 font-medium">{{ $game->users_count }} tracking</div>
                    </div>
                </a>
                @empty
                <div class="p-4 text-center text-gray-500">No trending games.</div>
                @endforelse
            </div>
        </aside>
    </div>
</div>
@endsection
