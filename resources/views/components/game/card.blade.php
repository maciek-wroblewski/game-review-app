<a href="/games/{{ $game->id }}" class="text-decoration-none">
    <div class="card h-100 shadow-sm border-0 position-relative group overflow-hidden">
        
        <!-- Banner Section: Takes full width, height scales with card width -->
        <div class="position-relative d-flex align-items-center justify-content-center overflow-hidden" style="aspect-ratio: 16 / 9; width: 100%;">
            <!-- Background Banner -->
            <img src="{{ asset($game->banner_img) }}" 
                class="position-absolute w-100 h-100 object-fit-cover" 
                style="z-index: 1; top: 0; left: 0;" 
                alt="{{ $game->title }}">
            
            <!-- Centered Logo -->
            <div class="position-relative p-3 d-flex justify-content-center align-items-center" style="z-index: 2; width: 80%;">
                <img src="{{ asset($game->logo) }}" 
                    style="max-width: 100%; height: auto; max-height: 80px; object-fit: contain; filter: drop-shadow(0 4px 8px rgba(0,0,0,0.8));" 
                    alt="Title Logo">
            </div>
        </div>

        <!-- Body Section: Overlaps the banner slightly -->
        <div class="card-body pt-0" style="margin-top: -12%; position: relative; z-index: 3;"> 
            <div class="d-flex align-items-end mb-3">
                
                <!-- Game Cover -->
                <div class="me-3 shadow-lg" style="width: 25%; min-width: 70px; max-width: 110px; flex-shrink: 0;">
                    <img src="{{ asset($game->cover_img) }}" 
                         class="rounded border border-2 border-white w-100" 
                         style="aspect-ratio: 2/3; object-fit: cover; background: #333;" 
                         alt="Cover">
                </div>
                
                <!-- Title & Rating -->
                <div class="pb-1 flex-grow-1 overflow-hidden">
                    <h5 class="card-title mb-0 fw-bold text-truncate w-100" style="color: #212529;">{{ $game->title }}</h5>
                    <div class="text-warning small d-flex align-items-center">
                        <i class="bi bi-star-fill me-1"></i> {{ number_format($game->average_rating, 1) }}
                        <span class="text-muted ms-2" style="font-size: 0.75rem;">({{ $game->posts_count ?? 0 }} posts)</span>
                    </div>
                </div>
            </div>

            <!-- Description -->
            <p class="card-text text-secondary small mb-3" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                {{ $game->details }}
            </p>

            <!-- Genres -->
            <div class="d-flex flex-wrap gap-1">
                @foreach($game->genres as $genre)
                    <a href="Genres/{{ $genre->id }}" class="text-decoration-none"><span class="badge rounded-pill border text-primary border-primary fw-normal" style="font-size: 0.65rem;">
                        {{ $genre->name }}
                    </span>
                    </a>
                @endforeach
            </div>
        </div>

        <!-- Footer -->
        <div class="card-footer bg-transparent border-top-0 pb-3 pt-0">
            <small class="text-muted" style="font-size: 0.75rem; font-style: italic;">
                By: 
                @foreach($game->credits as $user)
                    <a href="Profile/{{ $user->id }}" class="text-decoration-none"><span class="text-decoration-none border-bottom border-secondary border-opacity-25">{{ $user->username }}</span></a>@if(!$loop->last), @endif
                @endforeach
            </small>
        </div>
    </div>
</a>