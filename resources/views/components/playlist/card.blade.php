@props(['playlist', 'layout' => 'full', 'backUrl' => null])

@php
$isCompact = $layout === 'compact';
$hasOwner = $playlist->users->count() > 0;
$owner = $hasOwner ? $playlist->users->first() : null;

// Determine Cover URL
$coverUrl = null;
if ($playlist->cover) {
$coverUrl = str_starts_with($playlist->cover, 'http')
? $playlist->cover
: asset('storage/' . $playlist->cover);
}

// Fallback Gradient Logic
$coverHash = md5($playlist->name . '-playlist-cover');
$color1 = '#' . substr($coverHash, 0, 6);
$color2 = '#' . substr($coverHash, 6, 6);
$gradientStyle = "background: linear-gradient(135deg, {$color1}, {$color2});";

// Extract Initials (Up to 2 letters)
$words = explode(' ', $playlist->name);
$initials = '';
foreach ($words as $word) {
if (!empty($word)) {
$initials .= strtoupper(substr($word, 0, 1));
}
if (strlen($initials) >= 2) break;
}

$gamesCount = $playlist->games_count ?? $playlist->games->count();
$backLink = $backUrl ?: ($owner ? url('/users/' . $owner->username . '/playlists') : 'javascript:history.back()');
@endphp
@if($playlist->is_public || (auth()->check() && $playlist->users->contains(auth()->id())))
@if($isCompact)
<div class="col-md-6 col-lg-4 animate-fade-in">
    <div class="card shadow-sm border-0 h-100">
        @if($coverUrl)
        <img src="{{ $coverUrl }}" class="card-img-top" alt="{{ $playlist->name }}"
            style="aspect-ratio: 1/1; object-fit: cover;">
        @else
        <div class="card-img-top d-flex align-items-center justify-content-center text-white"
            style="aspect-ratio: 1/1; {{ $gradientStyle }}">
            <span class="fw-bold opacity-75" style="font-size: 4rem; text-shadow: 0 2px 10px rgba(0,0,0,0.2);">
                {{ $initials }}
            </span>
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
                    <a href="/playlists/{{ $playlist->id }}/edit" class="btn btn-sm btn-outline-primary"
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
                <img src="{{ $coverUrl }}" alt="{{ $playlist->name }}" class="img-fluid w-100 h-100"
                    style="aspect-ratio: 1/1; object-fit: cover;">
                @else
                <div class="h-100 w-100 d-flex align-items-center justify-content-center text-white"
                    style="aspect-ratio: 1/1; min-height: 220px; {{ $gradientStyle }}">
                    <span class="fw-bold opacity-75" style="font-size: 6rem; text-shadow: 0 2px 15px rgba(0,0,0,0.2);">
                        {{ $initials }}
                    </span>
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
                                Created by <a href="/users/{{ $owner->username }}" class="text-decoration-none">{{
                                    $owner->username }}</a>
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
                                <small class="text-muted">{{ \Illuminate\Support\Str::plural('Game', $gamesCount)
                                    }}</small>
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
                                <span class="d-block fs-4 fw-semibold">{{ $playlist->created_at->format('F j, Y')
                                    }}</span>
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
@endif