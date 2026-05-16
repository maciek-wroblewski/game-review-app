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
            @if($averageRating)
            <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center shadow-lg"
                style="width: 100px; height: 100px; border: 4px solid white;">
                <div class="text-center">
                    <span class="fs-2 fw-bold d-block lh-1">{{ $averageRating }}</span>
                    <span class="small fw-bold text-uppercase" style="font-size: 0.7rem;">Score</span>
                </div>
            </div>
            @endif
        </div>
    </div>

    <div class="container">
        <div class="row">
            <!-- Left Column: Details -->
                <x-game.details :game='$game' :playlists='$playlists'/>

            <!-- Right Column: Reviews -->
            <div class="col-md-8">
                <!-- Write / Edit Review Form -->
                @auth
                @if($userReviewPost)
                <x-post :post="$userReviewPost" />
                @else
                <x-post.create-form :hub-type="get_class($game)" :hub-id="$game->id" review-type="recommendation" />
                @endif
                @endauth

                <!-- Existing Reviews List (Now using components) -->
                <h3 class="mb-4 fw-bold">Player Reviews <span class="text-muted fs-5 fw-normal">({{
                        $game->posts->count() }})</span></h3>

                @forelse($game->posts as $post)
                @php
                $post->review->setRelation('post', $post);
                @endphp
                @continue($post == $userReviewPost)
                <x-post :post="$post" />
                @empty
                <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                    <h5 class="mb-0">No reviews yet! Be the first to share your thoughts above.</h5>
                </div>
                @endforelse

            </div>
        </div>
    </div>
</x-layout>