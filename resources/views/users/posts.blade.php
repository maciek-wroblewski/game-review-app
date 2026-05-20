<x-layout headtitle="{{ $user->username }} Posts">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-5">
            <div>
                <h1 class="fw-bold mb-1">{{ __('users.posts_title') }}</h1>
                <p class="text-muted mb-0">{{ __('users.posts_desc', ['username' => $user->username]) }}</p>
            </div>
            <a href="/users/{{ $user->username }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> {{ __('common.back_to_profile') }}
            </a>
        </div>

        @if($posts->isEmpty())
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h4 class="fw-bold mb-2">{{ __('users.no_posts_yet') }}</h4>
                <p class="mb-0">{{ __('users.no_community_posts') }}</p>
            </div>
        @else
            <x-post.list :posts="$posts" />
        @endif
    </div>
</x-layout>