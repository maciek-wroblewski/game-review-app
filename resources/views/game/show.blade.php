@extends('layouts.app')

@section('title', 'GameHub - ' . $game->title)

@section('content')
<div class="space-y-8">
    
    <!-- Hero Banner Tile -->
    <section class="bg-gray-800 rounded-xl border border-gray-700 shadow-2xl overflow-hidden relative">
        <div class="absolute inset-0 bg-cover bg-center opacity-40" style="background-image: url('{{ $game->banner_image_url ?? 'https://images.unsplash.com/photo-1542751371-adc38448a05e?auto=format&fit=crop&w=1600&q=80' }}');"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/80 to-transparent"></div>
        
        <div class="relative z-10 p-6 md:p-10 flex flex-col md:flex-row items-end md:items-stretch gap-8 h-full min-h-[400px]">
            <!-- Cover Art -->
            <div class="w-48 md:w-64 flex-shrink-0 self-center md:self-end mt-20 md:mt-0 shadow-2xl rounded-lg overflow-hidden border-2 border-gray-700">
                <img src="{{ $game->cover_image_url ?? 'https://images.unsplash.com/photo-1550745165-9bc0b252726f?auto=format&fit=crop&w=400&q=80' }}" class="w-full h-auto object-cover">
            </div>
            
            <!-- Game Info -->
            <div class="flex-1 self-end pb-2">
                <div class="flex flex-wrap gap-2 mb-3">
                    @foreach($game->genres as $genre)
                        <span class="bg-indigo-600/80 backdrop-blur text-white text-xs px-2 py-1 rounded font-medium shadow">{{ $genre->name }}</span>
                    @endforeach
                </div>
                <h1 class="text-4xl md:text-6xl font-black text-white tracking-tight drop-shadow-md mb-2">{{ $game->title }}</h1>
                <div class="text-gray-300 font-medium flex items-center space-x-4 mb-4">
                    <span>{{ $game->release_date ? \Carbon\Carbon::parse($game->release_date)->format('Y') : 'TBA' }}</span>
                    <span>•</span>
                    <span>{{ $game->developers->first()->name ?? 'Unknown Developer' }}</span>
                </div>
                
                <!-- Interaction Bar -->
                <div class="flex flex-wrap items-center gap-4 mt-6">
                    <!-- Status Dropdown (Alpine) -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold py-3 px-6 rounded-lg shadow-lg flex items-center gap-2 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                            Log Game
                        </button>
                        <div x-show="open" @click.away="open = false" class="absolute left-0 mt-2 w-56 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50 overflow-hidden" style="display: none;">
                            <form action="#" method="POST" class="flex flex-col text-sm">
                                @csrf
                                <button type="submit" name="status" value="playing" class="text-left px-4 py-3 hover:bg-gray-700 text-white border-b border-gray-700">Currently Playing</button>
                                <button type="submit" name="status" value="played" class="text-left px-4 py-3 hover:bg-gray-700 text-white border-b border-gray-700">Played</button>
                                <button type="submit" name="status" value="wishlisted" class="text-left px-4 py-3 hover:bg-gray-700 text-white border-b border-gray-700">Wishlist</button>
                                <button type="submit" name="status" value="dropped" class="text-left px-4 py-3 hover:bg-gray-700 text-white">Dropped</button>
                            </form>
                        </div>
                    </div>
                    
                    <!-- 10-Point Rating (Icon + Number) -->
                    <button class="bg-gray-800/80 hover:bg-gray-700 border border-gray-600 text-white py-3 px-4 rounded-lg shadow flex items-center gap-2 transition backdrop-blur group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-yellow-400 transition" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        <span class="font-bold">Rate</span>
                    </button>
                </div>
            </div>
            
            <!-- Aggregated Score Tile -->
            <div class="hidden lg:flex flex-col gap-3 self-end">
                <div class="bg-gray-900/80 backdrop-blur border border-gray-700 p-4 rounded-xl text-center shadow-lg min-w-[120px]">
                    <div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-1">Avg Score</div>
                    <div class="text-4xl font-black text-indigo-400">{{ $averageRating }}</div>
                </div>
                <div class="bg-gray-900/80 backdrop-blur border border-gray-700 p-4 rounded-xl text-center shadow-lg min-w-[120px]">
                    <div class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-1">Recommended</div>
                    <div class="text-3xl font-black {{ $recommendationPercent >= 70 ? 'text-green-400' : ($recommendationPercent >= 40 ? 'text-yellow-400' : 'text-red-400') }}">
                        {{ $recommendationPercent }}{{ $recommendationPercent !== 'N/A' ? '%' : '' }}
                    </div>
                </div>
            </div>
        </div>
    </section>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Content (Left, 2/3) -->
        <div class="lg:col-span-2 space-y-8">
            <!-- Summary -->
            <section class="bg-gray-800 rounded-xl border border-gray-700 p-6 shadow-md">
                <h3 class="text-xl font-bold text-white mb-4 flex items-center">
                    <span class="bg-indigo-500 w-2 h-6 rounded mr-3"></span> Summary
                </h3>
                <p class="text-gray-300 leading-relaxed">{{ $game->summary ?? 'No description available for this game.' }}</p>
            </section>
            
            <!-- The Hub (Posts/Reviews) -->
            <section class="bg-gray-800 rounded-xl border border-gray-700 shadow-md overflow-hidden">
                <div class="p-6 border-b border-gray-700 flex justify-between items-center bg-gray-800/50">
                    <h3 class="text-xl font-bold text-white flex items-center">
                        <span class="bg-green-500 w-2 h-6 rounded mr-3"></span> Community Hub
                    </h3>
                    <button class="bg-indigo-600 hover:bg-indigo-500 text-white px-4 py-2 rounded font-bold text-sm transition">Write Review</button>
                </div>
                
                <div class="divide-y divide-gray-700">
                    @forelse($posts as $post)
                        <div class="p-6 hover:bg-gray-750 transition">
                            <div class="flex items-center space-x-3 mb-4">
                                <img src="{{ $post->user->avatar_url ?? 'https://ui-avatars.com/api/?name='.urlencode($post->user->name) }}" class="w-10 h-10 rounded-full border border-gray-600">
                                <div>
                                    <div class="font-bold text-white">{{ $post->user->name }}</div>
                                    <div class="text-xs text-gray-500">{{ $post->created_at->diffForHumans() }}</div>
                                </div>
                                <!-- 3-Point Recommendation Badge (Dummy data for layout) -->
                                <div class="ml-auto flex items-center gap-1 bg-green-500/10 text-green-400 px-3 py-1 rounded-full border border-green-500/20">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                    <span class="text-xs font-bold uppercase tracking-wider">Recommended</span>
                                </div>
                            </div>
                            <h4 class="text-lg font-bold text-gray-200 mb-2">{{ $post->title }}</h4>
                            <p class="text-gray-400 text-sm mb-4">{{ $post->body }}</p>
                            
                            <div class="flex gap-4">
                                <form method="POST" action="{{ route('post.upvote', $post) }}">
                                    @csrf
                                    <button class="flex items-center space-x-1.5 text-gray-500 hover:text-indigo-400 text-sm font-medium transition">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                                        <span>{{ $post->upvotes }} Helpful</span>
                                    </button>
                                </form>
                                <button class="flex items-center space-x-1.5 text-gray-500 hover:text-indigo-400 text-sm font-medium transition">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    <span>{{ $post->comments->count() }} Comments</span>
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center text-gray-500">No reviews or posts yet. Be the first to share your thoughts!</div>
                    @endforelse
                </div>
            </section>
        </div>
        
        <!-- Sidebar (Right, 1/3) -->
        <div class="space-y-6">
            <!-- Write Review / 3-Point System CTA -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 shadow-md text-center">
                <h4 class="text-gray-300 font-bold mb-4">Would you recommend this game?</h4>
                <div class="grid grid-cols-3 gap-2">
                    <button class="bg-gray-700 hover:bg-green-600/20 text-gray-400 hover:text-green-400 border border-gray-600 hover:border-green-500 py-3 rounded-lg flex flex-col items-center gap-1 transition group">
                        <svg class="w-6 h-6 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"></path></svg>
                        <span class="text-xs font-bold uppercase">Yes</span>
                    </button>
                    <button class="bg-gray-700 hover:bg-yellow-600/20 text-gray-400 hover:text-yellow-400 border border-gray-600 hover:border-yellow-500 py-3 rounded-lg flex flex-col items-center gap-1 transition group">
                        <svg class="w-6 h-6 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"></path></svg>
                        <span class="text-xs font-bold uppercase">Meh</span>
                    </button>
                    <button class="bg-gray-700 hover:bg-red-600/20 text-gray-400 hover:text-red-400 border border-gray-600 hover:border-red-500 py-3 rounded-lg flex flex-col items-center gap-1 transition group">
                        <svg class="w-6 h-6 group-hover:translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14H5.236a2 2 0 01-1.789-2.894l3.5-7A2 2 0 018.736 3h4.018a2 2 0 01.485.06l3.76.94m-7 10v5a2 2 0 002 2h.096c.5 0 .905-.405.905-.904 0-.715.211-1.413.608-2.008L17 13V4m-7 10h2m5-10h2a2 2 0 012 2v6a2 2 0 01-2 2h-2.5"></path></svg>
                        <span class="text-xs font-bold uppercase">No</span>
                    </button>
                </div>
            </div>

            <!-- Details -->
            <div class="bg-gray-800 rounded-xl border border-gray-700 p-6 shadow-md">
                <h3 class="text-lg font-bold text-white mb-4 border-b border-gray-700 pb-2">Information</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Platforms</dt>
                        <dd class="text-gray-300 text-right">
                            {{ $game->platforms->pluck('name')->join(', ') ?: 'Unknown' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Publishers</dt>
                        <dd class="text-gray-300 text-right">
                            {{ $game->publishers->pluck('name')->join(', ') ?: 'Unknown' }}
                        </dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Developers</dt>
                        <dd class="text-gray-300 text-right">
                            {{ $game->developers->pluck('name')->join(', ') ?: 'Unknown' }}
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
