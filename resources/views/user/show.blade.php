@extends('layouts.app')

@section('title', 'GameHub - ' . $user->name)

@section('content')
<div class="space-y-8">
    
    <!-- Profile Header Tile -->
    <section class="bg-gray-800 rounded-xl p-8 border border-gray-700 shadow-md relative overflow-hidden">
        <!-- Profile Background -->
        <div class="absolute inset-0 bg-cover bg-center opacity-30" style="background-image: url('{{ $user->background_url ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=1600&q=80' }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-gray-900 to-transparent pointer-events-none"></div>
        
        <div class="flex flex-col md:flex-row items-center md:items-start relative z-10">
            <img src="{{ $user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($user->name).'&background=random&size=128' }}" class="w-32 h-32 rounded-full border-4 border-gray-900 shadow-xl mb-4 md:mb-0 md:mr-8">
            <div class="flex-1 text-center md:text-left">
                <h2 class="text-4xl font-black text-white tracking-tight">{{ $user->name }}</h2>
                <p class="text-gray-400 mt-2 max-w-xl mx-auto md:mx-0">{{ $user->bio ?? 'No bio provided yet. Just a gamer.' }}</p>
                
                @auth
                    @if(auth()->id() !== $user->id)
                        <div class="mt-4 space-x-2">
                            <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-6 py-2 rounded-lg font-bold transition shadow-lg text-sm">Follow</button>
                            <button class="bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-lg text-sm border border-gray-600">Message</button>
                        </div>
                    @else
                        <button class="mt-4 bg-gray-700 hover:bg-gray-600 text-white px-4 py-2 rounded-lg font-medium transition shadow-lg text-sm border border-gray-600">Edit Profile & Background</button>
                    @endif
                @endauth
            </div>
            
            <!-- Stats Grid -->
            <div class="grid grid-cols-3 gap-4 mt-8 md:mt-0 bg-gray-900/80 backdrop-blur p-4 rounded-xl border border-gray-700/50 w-full md:w-auto">
                <div class="text-center">
                    <div class="text-3xl font-black text-white">{{ $stats['games_played'] }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Played</div>
                </div>
                <div class="text-center border-l border-r border-gray-700/50 px-4">
                    <div class="text-3xl font-black text-indigo-400">{{ $stats['average_rating'] }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Avg Rate</div>
                </div>
                <div class="text-center">
                    <div class="text-3xl font-black text-white">{{ $stats['posts_count'] }}</div>
                    <div class="text-xs text-gray-400 uppercase tracking-wider font-semibold">Posts</div>
                </div>
            </div>
        </div>
    </section>

    <!-- Interactive Tabs Section -->
    <section x-data="{ tab: 'library', libraryFilter: 'played' }">
        <div class="flex space-x-4 border-b border-gray-700 mb-6 pb-2 overflow-x-auto">
            <button @click="tab = 'library'" :class="tab === 'library' ? 'text-indigo-400 font-bold border-b-2 border-indigo-400' : 'text-gray-400 hover:text-gray-300 font-medium'" class="px-4 py-2 transition">Game Library</button>
            <button @click="tab = 'custom'" :class="tab === 'custom' ? 'text-indigo-400 font-bold border-b-2 border-indigo-400' : 'text-gray-400 hover:text-gray-300 font-medium'" class="px-4 py-2 transition flex items-center">
                Custom Lists 
                @auth @if(auth()->id() === $user->id) <span class="ml-2 bg-gray-700 text-xs px-2 rounded hover:bg-indigo-500 hover:text-white transition">+ New</span> @endif @endauth
            </button>
            <button @click="tab = 'friends'" :class="tab === 'friends' ? 'text-indigo-400 font-bold border-b-2 border-indigo-400' : 'text-gray-400 hover:text-gray-300 font-medium'" class="px-4 py-2 transition">Friends (0)</button>
        </div>
        
        <!-- Library Tab -->
        <div x-show="tab === 'library'">
            <div class="flex space-x-2 mb-4">
                <button @click="libraryFilter = 'played'" :class="libraryFilter === 'played' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'" class="px-3 py-1 text-sm rounded transition">Played ({{ $lists['played']->count() }})</button>
                <button @click="libraryFilter = 'playing'" :class="libraryFilter === 'playing' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'" class="px-3 py-1 text-sm rounded transition">Playing ({{ $lists['playing']->count() }})</button>
                <button @click="libraryFilter = 'wishlisted'" :class="libraryFilter === 'wishlisted' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'" class="px-3 py-1 text-sm rounded transition">Wishlist ({{ $lists['wishlisted']->count() }})</button>
                <button @click="libraryFilter = 'dropped'" :class="libraryFilter === 'dropped' ? 'bg-indigo-600 text-white' : 'bg-gray-800 text-gray-400 hover:bg-gray-700'" class="px-3 py-1 text-sm rounded transition">Dropped ({{ $lists['dropped']->count() }})</button>
            </div>

            <!-- Library Content Wrapper. For simplicity here, we're just showing the 'played' list as proof of concept -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" x-show="libraryFilter === 'played'">
                @forelse($lists['played'] as $game)
                    <div class="bg-gray-800 rounded-xl overflow-hidden border border-gray-700 hover:border-gray-600 transition shadow-md group">
                        <div class="relative h-48 bg-gray-900">
                            <img src="{{ $game->banner_image_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=600&q=80' }}" class="w-full h-full object-cover opacity-70 group-hover:opacity-100 transition duration-300">
                            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-transparent to-transparent"></div>
                            <div class="absolute bottom-4 left-4 right-4">
                                <a href="{{ route('game.show', $game->slug) }}" class="text-xl font-bold text-white hover:underline">{{ $game->title }}</a>
                            </div>
                            <div class="absolute top-4 right-4 bg-gray-900/80 backdrop-blur text-white font-black px-3 py-2 rounded-lg border border-gray-700 flex items-center justify-center min-w-[3rem]">
                                @if($game->pivot->personal_rating)
                                    <span class="text-indigo-400">{{ $game->pivot->personal_rating }}</span><span class="text-xs text-gray-400 ml-1">/10</span>
                                @else
                                    -
                                @endif
                            </div>
                        </div>
                        @if($game->pivot->review_text)
                        <div class="p-4 bg-gray-800/50 border-t border-gray-700 text-sm text-gray-300 italic">
                            "{{ Str::limit($game->pivot->review_text, 100) }}"
                        </div>
                        @endif
                    </div>
                @empty
                    <div class="col-span-full py-12 text-center text-gray-500 bg-gray-800/50 rounded-xl border border-gray-700 border-dashed">
                        This list is empty.
                    </div>
                @endforelse
            </div>
            
            <div x-show="libraryFilter !== 'played'" class="py-12 text-center text-gray-500 bg-gray-800/50 rounded-xl border border-gray-700 border-dashed">
                Select a category to view games.
            </div>
        </div>
        
        <!-- Custom Lists Tab -->
        <div x-show="tab === 'custom'" style="display: none;">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Example Placeholder for Custom List -->
                <div class="bg-gray-800 rounded-xl p-6 border border-gray-700 hover:border-indigo-500 cursor-pointer transition shadow-md group">
                    <h4 class="text-xl font-bold text-white group-hover:text-indigo-400">Top 10 Indie Masterpieces</h4>
                    <p class="text-gray-400 text-sm mt-2">A collaborative list with Bob detailing our favorite indie games of all time.</p>
                    <div class="mt-4 flex items-center justify-between text-sm text-gray-500">
                        <span>10 Games</span>
                        <div class="flex -space-x-2">
                            <img src="https://ui-avatars.com/api/?name=Alice&background=random" class="w-6 h-6 rounded-full border border-gray-800">
                            <img src="https://ui-avatars.com/api/?name=Bob&background=random" class="w-6 h-6 rounded-full border border-gray-800">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Friends Tab -->
        <div x-show="tab === 'friends'" style="display: none;">
            <div class="py-12 text-center text-gray-500 bg-gray-800/50 rounded-xl border border-gray-700 border-dashed">
                No friends added yet.
            </div>
        </div>
        
    </section>

</div>
@endsection
