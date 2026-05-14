<x-layout headtitle="{{ $user->username }} Playlists">

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h1 class="fw-bold mb-1">
                    Playlists
                </h1>

                <p class="text-muted mb-0">
                    Public playlists created by {{ $user->username }}
                </p>

            </div>

            <a href="/users/{{ $user->username }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-2"></i>
                Back to Profile

            </a>

        </div>

        <div class="row g-4">

            @forelse($playlists as $playlist)

                <div class="col-md-6 col-lg-4">

                    <div class="card shadow-sm border-0 h-100">

                        <div class="card-body d-flex flex-column">

                            <div class="mb-3">

                                <h3 class="fw-bold mb-2">
                                    {{ $playlist->name }}
                                </h3>

                                <p class="text-muted mb-0">

                                    {{ $playlist->description ?? 'No description provided.' }}

                                </p>

                            </div>

                            <div class="mt-auto d-flex justify-content-between align-items-center">

                                <small class="text-muted">

                                    {{ $playlist->games->count() }} games

                                </small>

                                <a href="/playlists/{{ $playlist->id }}"
                                   class="btn btn-primary btn-sm">

                                    View Playlist

                                </a>

                            </div>

                        </div>

                    </div>

                </div>

            @empty

                <div class="col-12">

                    <div class="alert alert-info border-0 shadow-sm p-4 text-center">

                        <h4 class="fw-bold mb-2">
                            No playlists yet
                        </h4>

                        <p class="mb-0">
                            This user has not created any playlists yet.
                        </p>

                    </div>

                </div>

            @endforelse

        </div>

    </div>

</x-layout>