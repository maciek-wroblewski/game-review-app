@if($trendingGames->isNotEmpty())
    <style>
        /* Modern fluid height: 
           Minimum 320px on phones, scales dynamically to 50% of screen height, maximum 500px on desktops */
        .trending-carousel-inner {
            height: clamp(320px, 50vh, 500px);
            border-radius: 0.75rem; /* Matches Bootstrap rounded-3 */
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
            background: linear-gradient(to top, rgba(0,0,0,0.95) 0%, rgba(0,0,0,0.5) 60%, rgba(0,0,0,0) 100%);
        }
    </style>

    <div id="trendingGamesCarousel" class="carousel slide mb-5 shadow-lg rounded" data-bs-ride="carousel">
        
        <!-- Carousel Indicators -->
        <div class="carousel-indicators">
            @foreach($trendingGames as $index => $game)
                <button type="button" 
                        data-bs-target="#trendingGamesCarousel" 
                        data-bs-slide-to="{{ $index }}" 
                        class="{{ $loop->first ? 'active' : '' }}" 
                        aria-current="{{ $loop->first ? 'true' : 'false' }}" 
                        aria-label="Slide {{ $index + 1 }}">
                </button>
            @endforeach
        </div>

        <div class="carousel-inner trending-carousel-inner overflow-hidden shadow-sm">
            @foreach($trendingGames as $game)
                <div class="carousel-item h-100 {{ $loop->first ? 'active' : '' }}">
                    <a href="/Games/{{ $game->id }}">
                    <img src="{{ asset($game->banner_img ?? $game->cover_img) }}" 
                         class="trending-carousel-img" 
                         alt="{{ $game->title }} cover">
                         
                    <div class="position-absolute top-0 start-0 w-100 h-100 carousel-gradient-overlay"></div>

                    <div class="carousel-caption text-start mb-2 mb-md-4 bottom-0 pb-4">
                        <div class="d-flex align-items-center gap-2 gap-md-3 mb-2">
                            @if($game->logo)
                                <img src="{{ asset($game->logo) }}" alt="logo" class="img-fluid" style="max-height: 40px; md:max-height: 60px;">
                            @else
                                <!-- Responsive typography: fs-4 on mobile, fs-2 on desktop -->
                                <h2 class="fw-bold mb-0 text-white fs-4 fs-md-2">{{ $game->title }}</h2>
                            @endif
                        </div>
                        
                        <!-- Responsive typography for the description -->
                        <p class="text-light mb-3 text-truncate-2 small fs-md-6 w-100 w-md-75">
                            {{ strip_tags($game->details) }}
                        </p>
                        
                        <div class="d-flex flex-wrap gap-2">
                            <span class="badge bg-danger px-2 py-1">🔥 Trending</span>
                            <span class="badge bg-dark bg-opacity-75 px-2 py-1 border border-secondary">Score: {{ number_format($game->trending_score, 1) }}</span>
                        </div>
                    </div>
                    </a>
                </div>
            @endforeach
        </div>

        <!-- Carousel Controls -->
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