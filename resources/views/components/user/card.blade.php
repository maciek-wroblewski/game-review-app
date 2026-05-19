@props(['user', 'layout' => 'full', 'interactive' => true])

@php
$isCompact = $layout === 'compact';
@endphp

<div class="user-card-component mb-4 d-flex flex-column row-gap-4">

    <div class="card shadow-sm border-0 overflow-hidden">

        <x-user.banner :user="$user" :layout="$layout" />

        <div class="card-body {{ $isCompact ? 'p-3' : 'p-4' }} position-relative">

            <div class="d-flex {{ $isCompact ? 'flex-column' : 'flex-column flex-lg-row gap-4 align-items-lg-end' }}">

                {{-- Avatar --}}
                <div class="{{ $isCompact ? 'text-center' : '' }}">

                    <x-user.static-avatar
                        :user="$user"
                        :layout="$layout"
                        :interactive="$interactive" />

                </div>

                <div class="flex-grow-1 {{ $isCompact ? 'mt-3 text-center' : '' }}">

                    <div class="d-flex {{ $isCompact ? 'flex-column gap-2' : 'flex-column flex-lg-row justify-content-between align-items-lg-start gap-4' }}">

                        <div>

                            <div class="d-flex align-items-center gap-2 {{ $isCompact ? 'justify-content-center' : '' }}">

                                <h1 class="{{ $isCompact ? 'h4' : 'display-4' }} fw-bold mb-1">

                                    <a href="/users/{{ $user->username }}"
                                       class="text-dark text-decoration-none">

                                        {{ $user->username }}

                                    </a>

                                </h1>

                                {{-- Verified Badge --}}
                                @if($user->verified)

                                    <span class="verified-badge"
                                          title="Verified User">

                                        <i class="bi bi-patch-check-fill"></i>

                                    </span>

                                @endif

                                {{-- Admin Badge --}}
                                @if($user->is_admin)

                                    <span class="badge rounded-pill bg-danger px-3 py-2 fw-semibold shadow-sm">

                                        ADMIN

                                    </span>

                                @endif

                            </div>

                            <p class="text-muted {{ $isCompact ? 'small mb-2' : 'mb-3' }}">

                                Member since {{ $user->created_at->format('F Y') }}

                            </p>

                        </div>

                        <div class="d-flex flex-column align-items-lg-end gap-2">

                            @auth

                                @if(auth()->id() === $user->id)

                                    <div class="d-flex gap-2 flex-wrap justify-content-center">

                                        <a href="/profile"
                                           class="btn btn-outline-primary {{ $isCompact ? 'btn-sm' : 'btn-lg px-4' }} fw-semibold shadow-sm">

                                            <i class="bi bi-gear-fill me-1"></i>
                                            Edit Profile

                                        </a>

                                        {{-- Admin Panel Button --}}
                                        @if(auth()->user()->is_admin)

                                            <a href="/admin"
                                               class="btn btn-dark {{ $isCompact ? 'btn-sm' : 'btn-lg px-4' }} fw-semibold shadow-sm">

                                                <i class="bi bi-shield-lock-fill me-1"></i>

                                                Admin Panel

                                            </a>

                                        @endif

                                    </div>

                                @else

                                    <x-follow-button
                                        :target-user="$user"
                                        :button-classes="$isCompact ? 'btn-sm px-4 fw-semibold' : 'btn-lg px-4 fw-semibold'" />

                                @endif

                            @endauth

                        </div>

                    </div>

                    @if(!$isCompact)

                        <div class="mt-4">

                            <div class="bg-light rounded-4 p-4 border">

                                <h5 class="fw-bold mb-3">
                                    About
                                </h5>

                                <p class="fs-5 mb-0 text-secondary">

                                    {{ $user->bio ?? 'This user has not written a bio yet.' }}

                                </p>

                            </div>

                        </div>

                    @endif

                </div>

            </div>

        </div>

        {{-- Compact Footer --}}
        @if($isCompact)

            <div class="card-footer bg-white border-top-0 d-flex justify-content-around py-3">

                <div class="text-center">

                    <div class="followers-count fw-bold fs-5"
                         data-user-id="{{ $user->id }}">

                        {{ $user->followers_count ?? 0 }}

                    </div>

                    <div class="text-muted small">
                        Followers
                    </div>

                </div>

                <div class="text-center">

                    <div class="fw-bold fs-5">

                        {{ $user->following_count ?? 0 }}

                    </div>

                    <div class="text-muted small">
                        Following
                    </div>

                </div>

                <div class="text-center">

                    <div class="fw-bold fs-5">

                        {{ $user->reviews_count ?? 0 }}

                    </div>

                    <div class="text-muted small">
                        Reviews
                    </div>

                </div>

            </div>

        @endif

    </div>

    {{-- Full Stats --}}
    @if(!$isCompact)

        <div class="row g-4 mb-5">

            {{-- Reviews --}}
            <div class="col">

                <a href="/users/{{ $user->username }}/reviews"
                   class="text-decoration-none">

                    <div class="card shadow-sm border-0 text-center h-100 hover-card">

                        <div class="card-body py-4">

                            <div class="mb-2">

                                <i class="bi bi-star-fill text-primary fs-1"></i>

                            </div>

                            <h2 class="fw-bold display-5 mb-1 text-dark">

                                {{ $user->reviews_count ?? 0 }}

                            </h2>

                            <p class="text-muted mb-0 fs-5">
                                Reviews
                            </p>

                        </div>

                    </div>

                </a>

            </div>

            {{-- Posts --}}
            <div class="col">

                <a href="/users/{{ $user->username }}/posts"
                   class="text-decoration-none">

                    <div class="card shadow-sm border-0 text-center h-100 hover-card">

                        <div class="card-body py-4">

                            <div class="mb-2">

                                <i class="bi bi-pencil-square text-info fs-1"></i>

                            </div>

                            <h2 class="fw-bold display-5 mb-1 text-dark">

                                {{ $user->community_posts_count ?? 0 }}

                            </h2>

                            <p class="text-muted mb-0 fs-5">
                                Posts
                            </p>

                        </div>

                    </div>

                </a>

            </div>

            {{-- Playlists --}}
            <div class="col">

                <a href="/users/{{ $user->username }}/playlists"
                   class="text-decoration-none">

                    <div class="card shadow-sm border-0 text-center h-100 hover-card">

                        <div class="card-body py-4">

                            <div class="mb-2">

                                <i class="bi bi-collection-play-fill text-success fs-1"></i>

                            </div>

                            <h2 class="fw-bold display-5 mb-1 text-dark">

                                {{ $user->playlists_count ?? 0 }}

                            </h2>

                            <p class="text-muted mb-0 fs-5">
                                Playlists
                            </p>

                        </div>

                    </div>

                </a>

            </div>

            {{-- Following --}}
            <div class="col">

                <a href="/users/{{ $user->username }}/following"
                   class="text-decoration-none">

                    <div class="card shadow-sm border-0 text-center h-100 hover-card">

                        <div class="card-body py-4">

                            <div class="mb-2">

                                <i class="bi bi-person-plus-fill text-warning fs-1"></i>

                            </div>

                            <h2 class="fw-bold display-5 mb-1 text-dark">

                                {{ $user->following_count ?? 0 }}

                            </h2>

                            <p class="text-muted mb-0 fs-5">
                                Following
                            </p>

                        </div>

                    </div>

                </a>

            </div>

            {{-- Followers --}}
            <div class="col">

                <a href="/users/{{ $user->username }}/followers"
                   class="text-decoration-none">

                    <div class="card shadow-sm border-0 text-center h-100 hover-card">

                        <div class="card-body py-4">

                            <div class="mb-2">

                                <i class="bi bi-people-fill text-danger fs-1"></i>

                            </div>

                            <h2 class="followers-count fw-bold display-5 mb-1 text-dark"
                                data-user-id="{{ $user->id }}">

                                {{ $user->followers_count ?? 0 }}

                            </h2>

                            <p class="text-muted mb-0 fs-5">
                                Followers
                            </p>

                        </div>

                    </div>

                </a>

            </div>

        </div>

    @endif

</div>

<style>

.followers-count {

    display: inline-block;

    transition:
        transform 0.15s ease-in,
        opacity 0.15s ease-in;

    transform-origin: center;
}

.followers-count.is-flipping {

    transform: rotateX(90deg);

    opacity: 0;
}

.verified-badge {

    color: #0d6efd;

    font-size: 1.1rem;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    transform: translateY(-2px);

    filter:
        drop-shadow(0 0 6px rgba(13,110,253,0.25));

    transition:
        transform 0.2s ease,
        filter 0.2s ease;
}

.verified-badge:hover {

    transform: translateY(-2px) scale(1.08);

    filter:
        drop-shadow(0 0 10px rgba(13,110,253,0.4));
}

</style>