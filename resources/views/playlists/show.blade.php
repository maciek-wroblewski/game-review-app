<x-layout headtitle="{{ $playlist->name }}">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">
                    {{ $playlist->name }}
                </h1>
                <p class="text-muted mb-0">
                    {{ $playlist->description ?? 'A collection of games' }}
                </p>
                @if($playlist->users->count() > 0)
                    <small class="text-muted">Created by <a href="/users/{{ $playlist->users->first()->username }}" class="text-decoration-none">{{ $playlist->users->first()->username }}</a></small>
                @endif
            </div>
            @if($playlist->users->count() > 0)
                <a href="/users/{{ $playlist->users->first()->username }}/playlists" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back to Playlists
                </a>
            @else
                <a href="javascript:history.back()" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-2"></i> Back
                </a>
            @endif
        </div>

        @if($playlist->games->count() > 0)
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                @foreach($playlist->games as $game)
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
    </div>
</x-layout>
