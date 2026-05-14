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

        @forelse($reviews as $post)

            <div class="card shadow-sm border-0 mb-4">

                <div class="card-body p-4">

                    <div class="d-flex justify-content-between align-items-start mb-3">

                        <div>

                            <h3 class="fw-bold mb-1">

                                {{ $post->review->game->title ?? 'Unknown Game' }}

                            </h3>

                            <small class="text-muted">

                                Posted {{ $post->created_at->diffForHumans() }}

                            </small>

                        </div>

                        @if($post->review)

                            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill">

                                {{ $post->review->rating }}/10

                            </span>

                        @endif

                    </div>

                    <p class="fs-5 text-secondary mb-0"
                       style="white-space: pre-line;">

                        {{ $post->body }}

                    </p>

                </div>

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

</x-layout>