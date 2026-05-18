<x-layout headtitle="{{ $user->username }} Playlists">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">Playlists</h1>
                <p class="text-muted mb-0">Public playlists created by {{ $user->username }}</p>
            </div>
            <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> Back to Profile
            </a>
        </div>

        @if($playlists->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">No playlists yet</h4>
                <p class="mb-0">This user has not created any playlists yet.</p>
            </div>
        @else
            <div id="playlists-grid-wrapper" class="row g-4">
                @foreach($playlists as $playlist)
                    @include('users.partials.playlist-card-wrapper', ['playlist' => $playlist])
                @endforeach
            </div>

            <x-load-more :paginator="$playlists" target="#playlists-grid-wrapper" />
        @endif
    </div>
</x-layout>