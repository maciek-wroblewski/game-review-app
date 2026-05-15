@props(['user', 'layout' => 'full'])

@php
    // Color generation logic
    $bannerHash = md5($user->username . '-banner');
    $bannerColor1 = '#' . substr($bannerHash, 0, 6);
    $bannerColor2 = '#' . substr($bannerHash, 6, 6);

    $avatarHash = md5($user->username . '-avatar');
    $avatarColor = '#' . substr($avatarHash, 0, 6);

    // Layout configuration
    $isCompact = $layout === 'compact';
    $bannerHeight = $isCompact ? '100px' : '220px';
    $avatarSize = $isCompact ? '80px' : '160px';
    $avatarMargin = $isCompact ? '-40px' : '-110px';
    $avatarFontSize = $isCompact ? '2.5rem' : '4.5rem';
@endphp

<div class="user-card-component mb-4">
    <div class="card shadow-sm border-0 overflow-hidden">
        
        @if($user->banner)
            <img src="{{ asset($user->banner) }}" class="w-100" style="height: {{ $bannerHeight }}; object-fit: cover;" alt="{{ $user->username }}'s Banner">
        @else
            <div style="height: {{ $bannerHeight }}; background: linear-gradient(135deg, {{ $bannerColor1 }} 0%, {{ $bannerColor2 }} 100%);"></div>
        @endif

        <div class="card-body {{ $isCompact ? 'p-3' : 'p-4' }} position-relative">
            <div class="d-flex {{ $isCompact ? 'flex-column' : 'flex-column flex-lg-row gap-4 align-items-lg-end' }}">
                
                <div class="{{ $isCompact ? 'text-center' : '' }}">
                    @if($user->avatar)
                        <img src="{{ asset($user->avatar) }}" 
                            class="rounded-circle shadow border border-4 border-white"
                            style="width: {{ $avatarSize }}; height: {{ $avatarSize }}; margin-top: {{ $avatarMargin }}; flex-shrink: 0; object-fit: cover; position: relative; z-index: 2;"
                            alt="{{ $user->username }}'s Avatar">
                    @else
                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center shadow border border-4 border-white {{ $isCompact ? 'mx-auto' : '' }}"
                            style="width: {{ $avatarSize }}; height: {{ $avatarSize }}; font-size: {{ $avatarFontSize }}; margin-top: {{ $avatarMargin }}; flex-shrink: 0; background-color: {{ $avatarColor }}; position: relative; z-index: 2;">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                    @endif
                </div>

                <div class="flex-grow-1 {{ $isCompact ? 'mt-3 text-center' : '' }}">
                    <div class="d-flex {{ $isCompact ? 'flex-column gap-2' : 'flex-column flex-lg-row justify-content-between align-items-lg-start gap-4' }}">
                        
                        <div>
                            <h1 class="{{ $isCompact ? 'h4' : 'display-4' }} fw-bold mb-1">
                                <a href="/users/{{ $user->username }}" class="text-dark text-decoration-none">{{ $user->username }}</a>
                            </h1>
                            <p class="text-muted {{ $isCompact ? 'small mb-2' : 'mb-3' }}">
                                Member since {{ $user->created_at->format('F Y') }}
                            </p>
                        </div>

                        <div>
                            @auth
                                @if(auth()->id() === $user->id)
                                    <a href="/profile" class="btn btn-outline-primary {{ $isCompact ? 'btn-sm' : 'btn-lg px-4' }} fw-semibold shadow-sm">
                                        <i class="bi bi-gear-fill me-1"></i> Edit Profile
                                    </a>
                                @else
                                    {{-- Using our new Follow Button Component --}}
                                    <x-follow-button :target-user="$user" :button-classes="$isCompact ? 'btn-sm px-4 fw-semibold' : 'btn-lg px-4 fw-semibold'" />
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
                    <div class="fw-bold fs-5">{{ $user->followers->count() }}</div>
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

                <a href="/users/{{ $user->username }}/reviews"
                class="text-decoration-none">

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

                <a href="/users/{{ $user->username }}/playlists"
                class="text-decoration-none">

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

                <a href="/users/{{ $user->username }}/following"
                   class="text-decoration-none">

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

                <a href="/users/{{ $user->username }}/followers"
                   class="text-decoration-none">

                    <div class="card shadow-sm border-0 text-center h-100 hover-card">

                        <div class="card-body py-4">

                            <div class="mb-2">
                                <i class="bi bi-people-fill text-danger fs-1"></i>
                            </div>

                            <h2 class="fw-bold display-5 mb-1 text-dark">
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