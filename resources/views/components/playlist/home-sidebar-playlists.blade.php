@props(['playlists'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3 d-flex align-items-center">
            <i class="bi bi-collection-play-fill text-success me-2"></i>
            {{ __('home.my_playlists') }}
        </h5>
        <div class="d-flex flex-column gap-2">
            @forelse($playlists as $playlist)
                <a href="{{ url('/playlists/' . $playlist->id) }}" class="d-flex justify-content-between align-items-center p-2 rounded text-decoration-none text-dark hover-bg-light" style="transition: background 0.2s;">
                    <span class="fw-semibold text-truncate" style="max-width: 200px;">{{ $playlist->name }}</span>
                    <span class="badge rounded-pill playlist-badge">{{ $playlist->games_count }} {{ trans_choice('games.genre', $playlist->games_count) }}</span>
                </a>
            @empty
                <div class="text-muted small text-center py-3">
                    No playlists created yet.
                </div>
            @endforelse
        </div>
    </div>
</div>
