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