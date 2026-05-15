<x-layout headtitle="{{ $user->username }}">

    <div class="container py-5">

        <!-- Profile Header -->
        <x-user-card :user="$user" />

        <!-- Recent Reviews -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-4 p-lg-5">
                <div class="d-flex justify-content-between align-items-center mb-5">
                    <div>
                        <h2 class="fw-bold mb-1">Recent Reviews</h2>
                        <p class="text-muted mb-0">
                            Latest thoughts and opinions from {{ $user->username }}
                        </p>
                    </div>
                </div>

                @forelse($user->posts as $post)
                    @if($post->isReview())
                        {{-- Renders the Review Wrapper with the rating meter --}}
                        <x-review :review="$post->review" />
                    @else
                        {{-- Renders a standard standalone post --}}
                        <x-post :post="$post" />
                    @endif
                @empty
                    <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                        <h4 class="fw-bold mb-2">No reviews yet</h4>
                        <p class="mb-0">This user has not posted any reviews yet.</p>
                    </div>
                @endforelse
            </div>
        </div>

</x-layout>