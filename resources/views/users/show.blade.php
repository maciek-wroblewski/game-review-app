<x-layout headtitle="{{ $user->username }}">

    <div class="container py-5">

        <!-- Profile Header -->
        <div class="card shadow-sm border-0 mb-4 overflow-hidden">

            <!-- Banner -->
            <div class="bg-dark"
                 style="
                    height: 220px;
                    background: linear-gradient(135deg, #0d6efd 0%, #111827 100%);
                 ">
            </div>

            <div class="card-body p-4 position-relative">

                <div class="d-flex flex-column flex-lg-row gap-4 align-items-lg-end">

                    <!-- Avatar -->
                    <div class="rounded-circle bg-primary text-white
                                d-flex align-items-center justify-content-center
                                shadow border border-4 border-white"
                         style="
                            width: 160px;
                            height: 160px;
                            font-size: 4.5rem;
                            margin-top: -110px;
                            flex-shrink: 0;
                         ">

                        {{ strtoupper(substr($user->username, 0, 1)) }}

                    </div>

                    <!-- Main Info -->
                    <div class="flex-grow-1">

                        <div class="d-flex flex-column flex-lg-row
                                    justify-content-between
                                    align-items-lg-start
                                    gap-4">

                            <div>

                                <!-- Username -->
                                <h1 class="display-4 fw-bold mb-2">
                                    {{ $user->username }}
                                </h1>

                                <!-- Join Date -->
                                <p class="text-muted mb-3">
                                    Member since {{ $user->created_at->format('F Y') }}
                                </p>

                            </div>

                            <!-- Action Buttons -->
                            <div>

                                @auth

                                    @if(auth()->id() === $user->id)

                                        <a href="/profile"
                                           class="btn btn-outline-primary btn-lg px-4 fw-semibold shadow-sm">

                                            <i class="bi bi-gear-fill me-2"></i>
                                            Edit Profile

                                        </a>

                                    @else

                                        <form action="/users/{{ $user->id }}/follow"
                                              method="POST">

                                            @csrf

                                            @if(auth()->user()->following->contains($user))

                                                <button class="btn btn-outline-danger btn-lg px-4 fw-semibold shadow-sm">

                                                    <i class="bi bi-person-dash-fill me-2"></i>
                                                    Unfollow

                                                </button>

                                            @else

                                                <button class="btn btn-primary btn-lg px-4 fw-semibold shadow-sm">

                                                    <i class="bi bi-person-plus-fill me-2"></i>
                                                    Follow

                                                </button>

                                            @endif

                                        </form>

                                    @endif

                                @endauth

                            </div>

                        </div>

                        <!-- Bio -->
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

                    </div>

                </div>

            </div>

        </div>

        <!-- Stats -->
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

        <!-- Recent Reviews -->
        <div class="card shadow-sm border-0">

            <div class="card-body p-4 p-lg-5">

                <div class="d-flex justify-content-between align-items-center mb-5">

                    <div>

                        <h2 class="fw-bold mb-1">
                            Recent Reviews
                        </h2>

                        <p class="text-muted mb-0">
                            Latest thoughts and opinions from {{ $user->username }}
                        </p>

                    </div>

                </div>

                @forelse($user->posts as $post)

                    <div class="border-bottom pb-4 mb-4">

                        <div class="d-flex justify-content-between align-items-start mb-3">

                            <div>

                                <h4 class="fw-bold mb-1">
                                    {{ $post->review->game->title ?? 'Unknown Game' }}
                                </h4>

                                <small class="text-muted">
                                    Posted {{ $post->created_at->diffForHumans() }}
                                </small>

                            </div>

                            @if($post->review)

                                <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill shadow-sm">

                                    {{ $post->review->rating }}/10

                                </span>

                            @endif

                        </div>

                        <p class="text-secondary fs-5 mb-0"
                           style="white-space: pre-line;">

                            {{ $post->body }}

                        </p>

                    </div>

                @empty

                    <div class="alert alert-info border-0 shadow-sm p-4 text-center">

                        <h4 class="fw-bold mb-2">
                            No reviews yet
                        </h4>

                        <p class="mb-0">
                            This user has not posted any reviews yet.
                        </p>

                    </div>

                @endforelse

            </div>

        </div>

    </div>

</x-layout>