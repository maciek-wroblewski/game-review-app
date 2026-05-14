<x-layout headtitle="{{ $user->username }} Following">

    <div class="container py-5">

        <div class="d-flex align-items-center justify-content-between mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Following
                </h1>

                <p class="text-muted mb-0">
                    Users followed by {{ $user->username }}
                </p>

            </div>

            <a href="/users/{{ $user->username }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-2"></i>
                Back to Profile

            </a>

        </div>

        <div class="row g-4">

            @forelse($following as $followedUser)

                <div class="col-md-6 col-lg-4">

                    <a href="/users/{{ $followedUser->username }}"
                       class="text-decoration-none">

                        <div class="card border-0 shadow-sm h-100">

                            <div class="card-body d-flex align-items-center gap-3">

                                <!-- Avatar -->
                                <div class="rounded-circle bg-primary text-white
                                            d-flex align-items-center justify-content-center"
                                     style="
                                        width: 70px;
                                        height: 70px;
                                        font-size: 2rem;
                                        flex-shrink: 0;
                                     ">

                                    {{ strtoupper(substr($followedUser->username, 0, 1)) }}

                                </div>

                                <!-- User Info -->
                                <div>

                                    <h4 class="fw-bold text-dark mb-1">
                                        {{ $followedUser->username }}
                                    </h4>

                                    <p class="text-muted mb-0">

                                        {{ $followedUser->bio ?? 'No bio yet.' }}

                                    </p>

                                </div>

                            </div>

                        </div>

                    </a>

                </div>

            @empty

                <div class="col-12">

                    <div class="alert alert-info border-0 shadow-sm p-4 text-center">

                        <h4 class="fw-bold mb-2">
                            Not following anyone yet
                        </h4>

                        <p class="mb-0">
                            This user is not following anyone yet.
                        </p>

                    </div>

                </div>

            @endforelse

        </div>

    </div>

</x-layout>