<x-layout headtitle="{{ $user->username }} Reviews">

    <div class="container py-5">

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="fw-bold mb-1">
                    Reviews
                </h1>
                <p class="text-muted mb-0">
                    Reviews written by {{ $user->username }}
                </p>
            </div>

            <a href="/users/{{ $user->username }}"
               class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i>
                Back to Profile
            </a>
        </div>

        {{-- Use your unified pagination component --}}
        @if($posts->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">
                    No reviews yet
                </h4>
                <p class="mb-0">
                    This user has not posted any reviews yet.
                </p>
            </div>
        @else
            <x-post.list :posts="$posts" />
        @endif

    </div>

</x-layout>