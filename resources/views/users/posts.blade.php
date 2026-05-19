<x-layout headtitle="{{ $user->username }} Posts">

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-5">

            <div>

                <h1 class="fw-bold mb-1">
                    Posts
                </h1>

                <p class="text-muted mb-0">
                    Community posts written by {{ $user->username }}
                </p>

            </div>

            <a href="/users/{{ $user->username }}"
               class="btn btn-outline-secondary">

                <i class="bi bi-arrow-left me-1"></i>
                Back to Profile

            </a>

        </div>

        @forelse($posts as $post)

            <div class="mb-4">

                <x-post :post="$post" />

            </div>

        @empty

            <div class="alert alert-info border-0 shadow-sm p-4 text-center">

                <h4 class="fw-bold mb-2">
                    No posts yet
                </h4>

                <p class="mb-0">
                    This user has not created any community posts yet.
                </p>

            </div>

        @endforelse

    </div>

</x-layout>