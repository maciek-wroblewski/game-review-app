@props(['user', 'layout' => 'full', 'interactive' => true])

@php
$isCompact = $layout === 'compact';
@endphp

<div class="user-card-component mb-4">
    <div class="card shadow-sm border-0 overflow-hidden">

        <x-user.banner :user="$user" :layout="$layout" />

        <div class="card-body {{ $isCompact ? 'p-3' : 'p-4' }} position-relative">
            <div class="d-flex {{ $isCompact ? 'flex-column' : 'flex-column flex-lg-row gap-4 align-items-lg-end' }}">
                {{-- Pass the interactive prop to the avatar --}}
                <div class="{{ $isCompact ? 'text-center' : '' }}">
                    <x-user.static-avatar :user="$user" :layout="$layout" :interactive="$interactive" />
                </div>
                <div class="flex-grow-1 {{ $isCompact ? 'mt-3 text-center' : '' }}">
                    <div
                        class="d-flex {{ $isCompact ? 'flex-column gap-2' : 'flex-column flex-lg-row justify-content-between align-items-lg-start gap-4' }}">

                        <div>
                            <h1 class="{{ $isCompact ? 'h4' : 'display-4' }} fw-bold mb-1">
                                <a href="/users/{{ $user->username }}" class="text-dark text-decoration-none">{{
                                    $user->username }}</a>
                            </h1>
                            <p class="text-muted {{ $isCompact ? 'small mb-2' : 'mb-3' }}">
                                Member since {{ $user->created_at->format('F Y') }}
                            </p>
                        </div>

                        <div>
                            @auth
                            @if(auth()->id() === $user->id)
                            <a href="/profile"
                                class="btn btn-outline-primary {{ $isCompact ? 'btn-sm' : 'btn-lg px-4' }} fw-semibold shadow-sm">
                                <i class="bi bi-gear-fill me-1"></i> Edit Profile
                            </a>
                            @else
                            {{-- Using our new Follow Button Component --}}
                            <x-follow-button :target-user="$user"
                                :button-classes="$isCompact ? 'btn-sm px-4 fw-semibold' : 'btn-lg px-4 fw-semibold'" />
                            @endif
                            @endauth
                        </div>

                    </div>

                    @if(!$isCompact)
                    <div class="mt-4">
                        <div class="bg-light rounded-4 p-4 border">
                            <h5 class="fw-bold mb-3">About</h5>
                            <p class="fs-5 mb-0 text-secondary">
                                {{ $user->bio ?? 'This user has not written a bio yet.' }}
                            </p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        @if($isCompact)
        <div class="card-footer bg-white border-top-0 d-flex justify-content-around py-3">
            <div class="text-center">
                <div class="followers-count fw-bold fs-5" data-user-id="{{ $user->id }}">{{ $user->followers->count() }}
                </div>
                <div class="text-muted small">Followers</div>
            </div>
            <div class="text-center">
                <div class="fw-bold fs-5">{{ $user->following->count() }}</div>
                <div class="text-muted small">Following</div>
            </div>
            <div class="text-center">
                <div class="fw-bold fs-5">{{ $user->posts->count() }}</div>
                <div class="text-muted small">Reviews</div>
            </div>
        </div>
        @endif
    </div>

    @if(!$isCompact)
    <div class="row g-4 mb-5">

        <!-- Reviews -->
        <div class="col-md-3">

            <a href="/users/{{ $user->username }}/reviews" class="text-decoration-none">

                <div class="card shadow-sm border-0 text-center h-100 hover-card">

                    <div class="card-body py-4">

                        <div class="mb-2">
                            <i class="bi bi-chat-left-text-fill text-primary fs-1"></i>
                        </div>

                        <h2 class="fw-bold display-5 mb-1 text-dark">
                            {{ $user->posts->count() }}
                        </h2>

                        <p class="text-muted mb-0 fs-5">
                            Reviews
                        </p>

                    </div>

                </div>

            </a>

        </div>

        <!-- Playlists -->
        <div class="col-md-3">

            <a href="/users/{{ $user->username }}/playlists" class="text-decoration-none">

                <div class="card shadow-sm border-0 text-center h-100 hover-card">

                    <div class="card-body py-4">

                        <div class="mb-2">
                            <i class="bi bi-collection-play-fill text-success fs-1"></i>
                        </div>

                        <h2 class="fw-bold display-5 mb-1 text-dark">
                            {{ $user->playlists->count() }}
                        </h2>

                        <p class="text-muted mb-0 fs-5">
                            Playlists
                        </p>

                    </div>

                </div>

            </a>

        </div>

        <!-- Following -->
        <div class="col-md-3">

            <a href="/users/{{ $user->username }}/following" class="text-decoration-none">

                <div class="card shadow-sm border-0 text-center h-100 hover-card">

                    <div class="card-body py-4">

                        <div class="mb-2">
                            <i class="bi bi-person-plus-fill text-warning fs-1"></i>
                        </div>

                        <h2 class="fw-bold display-5 mb-1 text-dark">
                            {{ $user->following->count() }}
                        </h2>

                        <p class="text-muted mb-0 fs-5">
                            Following
                        </p>

                    </div>

                </div>

            </a>

        </div>

        <!-- Followers -->
        <div class="col-md-3">

            <a href="/users/{{ $user->username }}/followers" class="text-decoration-none">

                <div class="card shadow-sm border-0 text-center h-100 hover-card">

                    <div class="card-body py-4">

                        <div class="mb-2">
                            <i class="bi bi-people-fill text-danger fs-1"></i>
                        </div>

                        <h2 class="followers-count fw-bold display-5 mb-1 text-dark" data-user-id="{{ $user->id }}">
                            {{ $user->followers->count() }}
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
        /* Required for transforms (rotate) to work on span elements */
        display: inline-block;

        /* 150ms going out, 150ms coming back = 300ms total animation */
        transition: transform 0.15s ease-in, opacity 0.15s ease-in;
        transform-origin: center;
    }

    /* This class will be toggled by JavaScript */
    .followers-count.is-flipping {
        transform: rotateX(90deg);
        /* Flips it flat so it's invisible */
        opacity: 0;
    }
</style>