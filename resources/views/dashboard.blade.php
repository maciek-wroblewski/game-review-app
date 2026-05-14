<x-layout headtitle="Dashboard">

    <div class="container py-5">

        <!-- Welcome Banner -->
        <div class="card border-0 shadow-sm overflow-hidden mb-5">

            <div class="bg-dark text-white p-5"
                 style="background: linear-gradient(135deg, #0d6efd 0%, #111827 100%);">

                <div class="row align-items-center">

                    <div class="col-lg-8">

                        <h1 class="display-5 fw-bold mb-3">
                            Welcome back, {{ auth()->user()->username }}
                        </h1>

                        <p class="fs-5 text-light opacity-75 mb-0">
                            Ready to discover new games and share your reviews?
                        </p>

                    </div>

                    <div class="col-lg-4 text-lg-end mt-4 mt-lg-0">

                        <i class="bi bi-controller display-1 text-primary"></i>

                    </div>

                </div>

            </div>

        </div>

        <!-- Quick Stats -->
        <div class="row g-4 mb-5">

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-5">

                        <i class="bi bi-chat-left-text-fill text-primary display-5 mb-3"></i>

                        <h2 class="fw-bold">
                            {{ auth()->user()->posts->count() }}
                        </h2>

                        <p class="text-muted mb-0">
                            Reviews Posted
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-5">

                        <i class="bi bi-collection-play-fill text-success display-5 mb-3"></i>

                        <h2 class="fw-bold">
                            {{ auth()->user()->playlists->count() }}
                        </h2>

                        <p class="text-muted mb-0">
                            Playlists Created
                        </p>

                    </div>

                </div>

            </div>

            <div class="col-md-4">

                <div class="card border-0 shadow-sm h-100">

                    <div class="card-body text-center py-5">

                        <i class="bi bi-people-fill text-warning display-5 mb-3"></i>

                        <h2 class="fw-bold">
                            {{ auth()->user()->following->count() }}
                        </h2>

                        <p class="text-muted mb-0">
                            Following
                        </p>

                    </div>

                </div>

            </div>

        </div>

        <!-- Quick Actions -->
        <div class="card border-0 shadow-sm">

            <div class="card-body p-5">

                <h2 class="fw-bold mb-4">
                    Quick Actions
                </h2>

                <div class="d-flex flex-wrap gap-3">

                    <a href="/Games"
                       class="btn btn-primary btn-lg">

                        <i class="bi bi-search me-2"></i>
                        Browse Games

                    </a>

                    <a href="/users/{{ auth()->user()->username }}"
                       class="btn btn-outline-dark btn-lg">

                        <i class="bi bi-person-circle me-2"></i>
                        View Profile

                    </a>

                    <a href="/profile"
                       class="btn btn-outline-primary btn-lg">

                        <i class="bi bi-gear-fill me-2"></i>
                        Edit Profile

                    </a>

                </div>

            </div>

        </div>

    </div>

</x-layout>