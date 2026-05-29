<x-layout headtitle="{{ $game->title }} - {{ __('reviews.discussions') }}">
    <div class="container-fluid p-0 mb-5"
        style="height: 400px; overflow: hidden; position: relative; background-color: #1a1a1a;">
        @if($game->banner_img)
        <img src="{{ $game->banner_img }}" alt="{{ $game->title }} Banner" class="w-100 h-100"
            style="object-fit: cover; opacity: 0.6;">
        @endif
        
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
                <p class="text-light fs-4 mb-0 opacity-75">{{ __('reviews.community_discussions') }}</p>
            </div>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <x-game.details :game='$game' :playlists='$playlists'/>

            <div class="col-md-8">
                
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h3 class="m-0 fw-bold">
                        {{ __('reviews.discussions') }}
                        <span class="text-muted fs-5 fw-normal">({{ $game->posts_count }})</span>
                    </h3>
                    <a href="/games/{{ $game->id }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i> {{ __('reviews.back_to_reviews') }}
                    </a>
                </div>

                    <x-post.create-form hub-type="game" :hub-id="$game->id" />

                <x-post.list :posts="$posts" />
            </div>
        </div>
    </div>
</x-layout>