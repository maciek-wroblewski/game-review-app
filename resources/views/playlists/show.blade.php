<x-layout headtitle="{{ $playlist->name }}">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">{{ $playlist->name }}</h1>
                <p class="text-muted mb-0">{{ $playlist->description ?? 'A collection of games' }}</p>
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

        @if($games->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center mt-5">
                <h4 class="fw-bold mb-2">Empty Playlist</h4>
                <p class="mb-0">There are no games in this playlist yet.</p>
            </div>
        @else
            <div id="playlist-games-wrapper" class="row row-cols-1 row-cols-md-2 row-cols-lg-4 g-4">
                @foreach($games as $game)
                    @include('playlists.partials.game-card-wrapper', ['game' => $game, 'playlist' => $playlist])
                @endforeach
            </div>
            
            <div class="mt-4">
                <x-load-more :paginator="$games" target="#playlist-games-wrapper" />
            </div>
        @endif
        
        <div class="mt-5 max-w-3xl">
            <x-hub-comments hub-type="playlist" :hub-id="$playlist->id" :posts="$posts" />
        </div>
    </div>
</x-layout>