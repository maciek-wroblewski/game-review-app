@props(['playlist', 'layout' => 'full', 'backUrl' => null])

@php
$isCompact = $layout === 'compact';
$hasOwner = $playlist->users->count() > 0;
$owner = $hasOwner ? $playlist->users->first() : null;
$coverUrl = $playlist->cover ? asset($playlist->cover) : null;
$gamesCount = $playlist->games_count ?? $playlist->games->count();
$backLink = $backUrl ?: ($owner ? url('/users/' . $owner->username . '/playlists') : 'javascript:history.back()');
@endphp

@if($isCompact)
<div class="col-md-6 col-lg-4 animate-fade-in">
    <div class="card shadow-sm border-0 h-100">
        @if($coverUrl)
        <img src="{{ $coverUrl }}" class="card-img-top" alt="{{ $playlist->name }}"
            style="aspect-ratio: 1/1; object-fit: cover;">
        @else
        <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-muted border-bottom"
            style="aspect-ratio: 1/1;">
            <i class="bi bi-collection-play" style="font-size: 4rem; opacity: 0.5;"></i>
        </div>
        @endif

        <div class="card-body d-flex flex-column">
            <div class="mb-3">
                <h3 class="fw-bold mb-2 text-truncate">{{ $playlist->name }}</h3>
                <p class="text-muted mb-0" style="min-height: 3rem;">
                    {{ $playlist->description ?? 'No description provided.' }}
                </p>
            </div>

            <div class="mt-auto d-flex justify-content-between align-items-center flex-wrap gap-2">
                <small class="text-muted">
                    {{ $gamesCount }} {{ \Illuminate\Support\Str::plural('game', $gamesCount) }}
                </small>

                <div class="d-flex gap-2 flex-wrap justify-content-end">
                    @auth
                        @if($playlist->users->contains(auth()->id()) && !$playlist->is_system)
                        <a href="/playlists/{{ $playlist->id }}/edit"
                            class="btn btn-sm btn-outline-primary"
                            title="Edit playlist">
                            <i class="bi bi-pencil"></i>
                        </a>
                        <form action="/playlists/{{ $playlist->id }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to delete this playlist?')"
                            class="d-inline-block">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger">
                                <i class="bi bi-trash"></i>
                            </button>
                        </form>
                        @endif
                    @endauth

                    <a href="/playlists/{{ $playlist->id }}" class="btn btn-primary btn-sm">
                        View
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@else
<div class="playlist-card-component animate-fade-in">
    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="row g-0 align-items-center">
            <div class="col-lg-4">
                @if($coverUrl)
                <img src="{{ $coverUrl }}" alt="{{ $playlist->name }}"
                    class="img-fluid w-100 h-100"
                    style="aspect-ratio: 1/1; object-fit: cover;">
                @else
                <div class="h-100 bg-light d-flex align-items-center justify-content-center text-muted border"
                    style="aspect-ratio: 1/1; min-height: 220px;">
                    <i class="bi bi-collection-play" style="font-size: 4rem; opacity: 0.5;"></i>
                </div>
                @endif
            </div>

            <div class="col-lg-8">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-xl-row justify-content-between gap-3">
                        <div class="min-w-0">
                            <h1 class="fw-bold mb-2">{{ $playlist->name }}</h1>
                            <p class="text-muted mb-3">
                                {{ $playlist->description ?? 'A collection of games' }}
                            </p>
                            @if($owner)
                            <p class="text-muted small mb-0">
                                Created by <a href="/users/{{ $owner->username }}"
                                    class="text-decoration-none">{{ $owner->username }}</a>
                            </p>
                            @endif
                        </div>

                        <a href="{{ $backLink }}" class="btn btn-outline-secondary align-self-start">
                            <i class="bi bi-arrow-left me-2"></i> Back
                        </a>
                    </div>

                    <div class="row row-cols-1 row-cols-sm-3 g-3 mt-4">
                        <div class="col">
                            <div class="bg-light rounded-4 p-3 h-100 text-center">
                                <span class="d-block fs-4 fw-semibold">{{ $gamesCount }}</span>
                                <small class="text-muted">{{ \Illuminate\Support\Str::plural('Game', $gamesCount) }}</small>
                            </div>
                        </div>

                        <div class="col">
                            <div class="bg-light rounded-4 p-3 h-100 text-center">
                                <span class="d-block fs-4 fw-semibold">{{ $playlist->users->count() }}</span>
                                <small class="text-muted">Owners</small>
                            </div>
                        </div>

                        <div class="col">
                            <div class="bg-light rounded-4 p-3 h-100 text-center">
                                <span class="d-block fs-4 fw-semibold">{{ $playlist->created_at->format('F j, Y') }}</span>
                                <small class="text-muted">Created</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
