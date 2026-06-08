<x-layout headtitle="{{ $user->username }} Followers">
    <div class="container py-5">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="fw-bold mb-1">{{ __('users.followers_title') }}</h1>
                <p class="text-muted mb-0">{{ __('users.followers_desc', ['username' => $user->username]) }}</p>
            </div>
            <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-2"></i> {{ __('common.back_to_profile') }}
            </a>
        </div>

        @if($followers->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">{{ __('users.no_followers_yet') }}</h4>
                <p class="mb-0">{{ __('users.no_followers_detail') }}</p>
            </div>
        @else
            <div id="followers-grid-wrapper" class="row g-4">
                @foreach($followers as $follower)
                    @include('users.partials.compact-card-wrapper', ['user' => $follower])
                @endforeach
            </div>

            <x-load-more :paginator="$followers" target="#followers-grid-wrapper" />
        @endif
    </div>
</x-layout>