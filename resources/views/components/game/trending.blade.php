<style>
:root {
    --carousel-height: 56.25vw; /* 16:9 Aspect Ratio */
    --carousel-max-height: 600px;
    --carousel-min-height: 350px;
    --gaming-primary: rgb(0, 145, 255); /* Cyberpunk Cyan - Change to your brand color */
    --transition-speed: 0.6s;
    --gaming-glow: rgba(0, 242, 255, 0.5);

}

/* 1. Container & Image Handling */
.trending-carousel {
    height: clamp(var(--carousel-min-height), var(--carousel-height), var(--carousel-max-height));
    border-radius: 1.5rem;
    overflow: hidden;
    background: #000;
}

.carousel-inner, .carousel-item, .carousel-link-wrapper {
    height: 100%;
}

.image-wrapper {
    position: relative;
    width: 100%;
    height: 100%;
}

.trending-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 10s ease; /* Slow zoom effect */
}

.carousel-item.active .trending-img {
    transform: scale(1.1);
}

/* 2. The Dynamic Overlay (Vignette + Blur) */
.carousel-overlay {
    position: absolute;
    inset: 0;
    /* Bottom-heavy gradient for text readability */
    background: linear-gradient(
        to top,
        rgba(0, 0, 0, 0.9) 0%,
        rgba(0, 0, 0, 0.4) 40%,
        transparent 80%
    );
    z-index: 1;
}

/* Modern Frosted Glass Footer */
.carousel-overlay::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 35%;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    mask-image: linear-gradient(to top, black, transparent);
    -webkit-mask-image: linear-gradient(to top, black, transparent);
    pointer-events: none;
}

/* 3. Content Positioning */
.carousel-caption {
    z-index: 2;
    bottom: 0;
    left: 0;
    right: 0;
    padding: 3rem 10% 4rem; /* Wide padding for cinematic feel */
    text-align: left !important;
}

.logo-area img {
    max-height: 15rem;
    max-width: 100%;
    object-fit: contain;
    filter: drop-shadow(0 5px 15px rgba(0,0,0,0.5));
}

.game-description {
    max-width: 600px;
    color: rgba(255,255,255,0.8);
    font-size: 1.1rem;
    line-height: 1.6;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    margin-top: 1rem;
}

/* 4. Custom Indicators (Modern Bars) */
.custom-indicators {
    margin-bottom: 1.5rem;
    z-index: 3;
}

.custom-indicators [data-bs-target] {
    width: 40px;
    height: 4px;
    border-radius: 2px;
    background-color: rgba(255,255,255,0.3);
    border: none;
    transition: all 0.3s ease;
}

/* Hover State for Indicators */
.custom-indicators [data-bs-target]:hover {
    background-color: rgba(255, 255, 255, 0.6);
}

.custom-indicators .active {
    background-color: var(--gaming-primary);
    width: 80px; /* Expand the active one */
    box-shadow: 0 0 15px var(--gaming-glow);
}

/* 5. Custom Controls (Hidden on mobile) */
.custom-ctrl {
    width: 5%;
    opacity: 0;
    transition: opacity 0.3s ease;
    z-index: 4;
}

.trending-carousel:hover .custom-ctrl {
    opacity: 1;
}

.control-icon {
    background: rgba(255,255,255,0.1);
    backdrop-filter: blur(5px);
    width: 45px;
    height: 45px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid rgba(255,255,255,0.2);
    font-size: 1.2rem;
}

/* 6. Animations */
.animate-in {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.8s cubic-bezier(0.2, 1, 0.3, 1);
}

.active .animate-in {
    opacity: 1;
    transform: translateY(0);
}

.delay-1 { transition-delay: 0.1s; }
.delay-2 { transition-delay: 0.2s; }

/* 7. Mobile Responsiveness */
@media (max-width: 768px) {
    .carousel-caption {
        padding: 2rem 1.5rem 3rem;
    }
    .logo-area img { max-height: 50px; }
    .game-description { display: none; } /* Kills description on mobile as requested */
    .custom-ctrl { display: none; }
}

/* --- 1. NEXT/PREV BUTTONS (Multi-state Animation) --- */
.custom-ctrl {
    width: 8%; /* Slightly wider hit area for the side controls */
    opacity: 0;
    transition: opacity 0.3s ease;
}

.trending-carousel:hover .custom-ctrl {
    opacity: 1;
}

.control-icon {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(8px);
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    border: 1px solid rgba(255, 255, 255, 0.2);
    color: white;
    font-size: 1.3rem;
    /* The "Spring" Transition */
    transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}

/* Hover State */
.custom-ctrl:hover .control-icon {
    background: var(--gaming-primary);
    color: #000;
    transform: scale(1.2) translateX(var(--hover-translate, 0));
    box-shadow: 0 0 25px var(--gaming-glow);
    border-color: transparent;
}

/* Specific direction nudges on hover */
.carousel-control-prev:hover { --hover-translate: -5px; }
.carousel-control-next:hover { --hover-translate: 5px; }

/* Active/Click State */
.custom-ctrl:active .control-icon {
    transform: scale(0.9);
    filter: brightness(0.8);
    transition: all 0.1s ease; /* Fast snap back */
}


</style>
<div id="trendingGamesCarousel" class="carousel slide trending-carousel shadow-lg" data-bs-ride="carousel">
    <!-- Indicators -->
    <div class="carousel-indicators custom-indicators">
        @foreach($trendingGames as $game)
            <button type="button" 
                data-bs-target="#trendingGamesCarousel" 
                data-bs-slide-to="{{ $loop->index }}" 
                class="{{ $loop->first ? 'active' : '' }}"
                aria-label="Slide {{ $loop->iteration }}">
            </button>
        @endforeach
    </div>

    <div class="carousel-inner">
        @foreach($trendingGames as $game)
            <div class="carousel-item {{ $loop->first ? 'active' : '' }}">
                <a href="{{ url('/games/' . $game->id) }}" class="carousel-link-wrapper d-block">
                    <div class="image-wrapper">
                        <img src="{{ asset($game->banner_img ?? ($game->cover_img ?? 'images/default-banner.jpg')) }}" 
                             class="trending-img" 
                             alt="{{ $game->title }}">
                        <div class="carousel-overlay"></div>
                    </div>
                    
                    <div class="carousel-caption">
                        <!-- Content remains the same as previous version -->
                        <div class="logo-area animate-in">
                            @if($game->logo)
                                <img src="{{ asset($game->logo) }}" alt="{{ $game->title }} logo">
                            @else
                                <h2 class="display-4 fw-bold text-white">{{ $game->title }}</h2>
                            @endif
                        </div>
                        <p class="game-description animate-in delay-1">
                            {{ Str::limit(strip_tags($game->details), 150) }}
                        </p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <!-- Controls -->
    <button class="carousel-control-prev custom-ctrl" type="button" data-bs-target="#trendingGamesCarousel" data-bs-slide="prev">
        <span class="control-icon"><i class="bi bi-chevron-left"></i></span>
    </button>
    <button class="carousel-control-next custom-ctrl" type="button" data-bs-target="#trendingGamesCarousel" data-bs-slide="next">
        <span class="control-icon"><i class="bi bi-chevron-right"></i></span>
    </button>
</div>