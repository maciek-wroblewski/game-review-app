@if($trendingGames->isNotEmpty())
<style>
    .trending-carousel-inner {
        height: clamp(320px, 50vh, 500px);
        border-radius: 0.75rem;
    }
    .trending-carousel-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        object-position: center;
    }
    .text-truncate-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }
    .carousel-gradient-overlay {
        background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.4) 60%, rgba(0,0,0,0) 100%);
    }
    .logo-container {
        height: 50px;
        display: flex;
        align-items: center;
    }
    .logo-container img {
        max-height: 100%;
        width: auto;
    }
</style>

<div id="trendingGamesCarousel" class="carousel slide mb-5 shadow-lg rounded" data-bs-ride="carousel">
    <!-- Indicators -->
    <div class="carousel-indicators">
        @foreach($trendingGames as $game)
            <button type="button" 
                data-bs-target="#trendingGamesCarousel" 
                data-bs-slide-to="{{ $loop->index }}" 
                class="{{ $loop->first ? 'active' : '' }}" 
                aria-current="{{ $loop->first ? 'true' : 'false' }}">
            </button>
        @endforeach
    </div>

    <div class="carousel-inner trending-carousel-inner overflow-hidden shadow-sm">
        @foreach($trendingGames as $game)
            <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                <a href="{{ url('/Games/' . $game->id) }}" class="text-decoration-none">
                    {{-- Banner Image --}}
                    <img src="{{ asset($game->banner_img ?? ($game->cover_img ?? 'images/default-banner.jpg')) }}" 
                         class="trending-carousel-img" 
                         alt="{{ $game->title }}">
                    
                    <div class="position-absolute top-0 start-0 w-100 h-100 carousel-gradient-overlay"></div>

                    <div class="carousel-caption text-start">
                        <div class="logo-container mb-2">
                            @if($game->logo)
                                <img src="{{ asset($game->logo) }}" alt="{{ $game->title }} logo">
                            @else
                                <h2 class="fw-bold mb-0 text-white fs-4 fs-md-2">{{ $game->title }}</h2>
                            @endif
                        </div>

                        <p class="text-light mb-3 text-truncate-2 small fs-md-6 w-100 w-md-75">
                            {{ strip_tags($game->details) }}
                        </p>

                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-danger px-2 py-1"><i class="fas fa-fire me-1"></i> Trending</span>
                            <span class="badge bg-dark bg-opacity-75 px-2 py-1 border border-secondary">
                                Score: {{ number_format($game->trending_score, 1) }}
                            </span>
                        </div>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Controls -->
    <button class="carousel-control-prev" type="button" data-bs-target="#trendingGamesCarousel" data-bs-slide="prev">
        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Previous</span>
    </button>
    <button class="carousel-control-next" type="button" data-bs-target="#trendingGamesCarousel" data-bs-slide="next">
        <span class="carousel-control-next-icon" aria-hidden="true"></span>
        <span class="visually-hidden">Next</span>
    </button>
</div>
@endif