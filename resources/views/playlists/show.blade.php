<x-layout headtitle="{{ $playlist->name }}">
    <div class="container py-5 d-flex flex-column gap-3">
        <x-playlist.card :playlist="$playlist" layout="full" />

        {{-- CHANGE 1: Use $games instead of $playlist->games --}}
        @if($games->count() > 0)
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
            
            {{-- CHANGE 2: Use $games instead of $playlist->games --}}
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
                        <i class="bi bi-trash"></i> Remove from List
                    </button>
                </form>
                @endif
                @endauth
            </div>
            @endforeach
        </div>
        @else
        <div class="alert alert-info border-0 shadow-sm p-4 text-center mt-5">
            <h4 class="fw-bold mb-2">Empty Playlist</h4>
            <p class="mb-0">There are no games in this playlist yet.</p>
        </div>
        @endif
        
        <div class="mt-5 max-w-3xl">
            <x-hub-comments hub-type="playlist" :hub-id="$playlist->id" :posts="$posts" />
        </div>
    </div>
</x-layout>