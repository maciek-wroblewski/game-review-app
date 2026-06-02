<x-layout headtitle="{{ $game->title }}">
    <!-- Banner Section -->
    <div class="container-fluid p-0 mb-5"
        style="height: 400px; overflow: hidden; position: relative; background-color: #1a1a1a;">
        @if($game->banner_img)
        <img src="{{ $game->banner_img }}" alt="{{ $game->title }} Banner" class="w-100 h-100"
            style="object-fit: cover; opacity: 0.6;">
        @endif
        <!-- Centered Logo -->
        @if($game->logo)
        <div class="position-absolute top-50 start-50 translate-middle w-100 px-3 text-center" style="z-index: 2;">
            <img src="{{ asset($game->logo) }}" alt="{{ $game->title }} Logo"
                style="max-height: 220px; max-width: 100%; object-fit: contain; filter: drop-shadow(0 4px 12px rgba(0,0,0,0.9));">
        </div>
        @endif

        <div class="position-absolute bottom-0 start-0 p-5 w-100 d-flex align-items-end justify-content-between"
            style="background: linear-gradient(transparent, rgba(0,0,0,0.9)); z-index: 3;">
            <div>
                <h1 class="display-3 fw-bold text-white mb-0">{{ $game->title }}</h1>
            </div>
            @if($game->average_rating)
            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                style="width: 100px; height: 100px; border: 4px solid white;">
                <div class="text-center">
                    <span class="fs-2 fw-bold d-block lh-1">{{ $game->average_rating }}</span>
                    <span class="small fw-bold text-uppercase" style="font-size: 0.7rem;">{{ __('reviews.score') }}</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Left Column: Details -->
            <x-game.details :game="$game" :playlists="$playlists" />

            <!-- Right Column: Reviews -->
            <div class="col-md-8">
                <!-- Write / Edit Review Form -->
                @if($userReviewPost)
                <x-post :post="$userReviewPost" />
                @else
                <x-post.create-form hub-type="game" :hub-id="$game->id" review-type="recommendation" />
                @endif

                <!-- Existing Reviews List (Now using components) -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="m-0 fw-bold">
                        {{ __('reviews.player_reviews') }}
                        <span class="text-muted fs-5 fw-normal">({{ $game->reviews_count }})</span>
                    </h3>
                    <a href="/games/{{ $game->id }}/discussions" class="btn btn-outline-secondary btn-sm">
                        <i class="bi bi-chat-text me-1"></i> {{ __('reviews.view_discussions') }}
                    </a>
                </div>

                <x-post.list :posts="$posts" />
            </div>
        </div>
    </div>
</x-layout>