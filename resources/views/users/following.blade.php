<x-layout headtitle="{{ $user->username }} Following">
    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="fw-bold mb-1">Following</h1>
                <p class="text-muted mb-0">Users followed by {{ $user->username }}</p>
            </div>
            <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> Back to Profile
            </a>
        </div>

        @if($following->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">Not following anyone yet</h4>
                <p class="mb-0">This user is not following anyone yet.</p>
            </div>
        @else
            <div id="following-grid-wrapper" class="row g-4">
                @foreach($following as $followedUser)
                    @include('users.partials.compact-card-wrapper', ['user' => $followedUser])
                @endforeach
            </div>

            <x-load-more :paginator="$following" target="#following-grid-wrapper" />
        @endif
    </div>
</x-layout>