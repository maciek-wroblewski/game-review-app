<x-layout headtitle="{{ $user->username }} Playlists">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="fw-bold mb-1">{{ __('users.playlists_title') }}</h1>
                <p class="text-muted mb-0">{{ __('users.playlists_desc', ['username' => $user->username]) }}</p>
            </div>
            
            <div class="d-flex gap-2">
                @if(auth()->id() === $user->id)
                    <a href="/playlists/create" class="btn btn-primary">
                        <i class="bi bi-plus-lg me-1"></i> {{ __('users.create_playlist') }}
                    </a>
                @endif
                <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> {{ __('common.back_to_profile') }}
                </a>
            </div>
        </div>

        @if($playlists->isEmpty())
        <div class="alert alert-info border-0 shadow-sm p-4 text-center">
            <h4 class="fw-bold mb-2">{{ __('users.no_playlists_yet') }}</h4>
            <p class="mb-0">{{ __('users.no_playlists_detail') }}</p>
        </div>
        @else
        <div id="playlists-grid-wrapper" class="row g-4">
            @foreach($playlists as $playlist)
            <x-playlist.card :playlist="$playlist" layout="compact" />
            @endforeach
        </div>

        <x-load-more :paginator="$playlists" target="#playlists-grid-wrapper" />
        @endif
    </div>
</x-layout>