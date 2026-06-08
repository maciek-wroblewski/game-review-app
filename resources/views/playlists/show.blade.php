<x-layout headtitle="{{ $playlist->name }}">
    <div class="container py-5 d-flex flex-column gap-3">
        <x-playlist.card :playlist="$playlist" layout="full" />

        @if($games->count() > 0)
        {{-- Added ID to wrapper for the load-more script to target --}}
        <div id="games-grid-wrapper" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            
            @foreach($games as $game)
            <div class="col d-flex flex-column gap-2">
                <div class="flex-grow-1">
                    <x-game-card :game="$game" />
                </div>

                @auth
                @if($playlist->users->contains(auth()->id()))
                <form action="/playlists/{{ $playlist->id }}/games/{{ $game->id }}" method="POST" class="d-grid mt-1">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <i class="bi bi-trash"></i> {{ __('playlists.remove_from_list') }}
                    </button>
                </form>
                @endif
                @endauth
            </div>
            @endforeach
        </div>

        {{-- Load More Component --}}
        <div class="mt-4">
            <x-load-more :paginator="$games" target="#games-grid-wrapper" />
        </div>
        
        @else
        <div class="alert alert-info border-0 shadow-sm p-4 text-center mt-5">
            <h4 class="fw-bold mb-2">{{ __('playlists.empty_playlist') }}</h4>
            <p class="mb-0">{{ __('playlists.no_games_yet') }}</p>
        </div>
        @endif
        
        <div class="mt-5 max-w-3xl">
            <x-hub-comments hub-type="playlist" :hub-id="$playlist->id" :posts="$posts" />
        </div>
    </div>
</x-layout>