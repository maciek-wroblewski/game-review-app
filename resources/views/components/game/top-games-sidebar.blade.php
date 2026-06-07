@props(['games'])

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <h5 class="fw-bold mb-3 d-flex align-items-center">
            <i class="bi bi-star-fill text-warning me-2"></i>
            {{ __('home.top_rated_games') }}
        </h5>
        
        <div class="d-flex flex-column gap-3">
            @forelse($games as $game)
                <a href="{{ url('/games/' . $game->id) }}" class="d-flex align-items-center text-decoration-none text-dark gap-2">
                    <div style="width: 45px; height: 60px; overflow: hidden; border-radius: 4px; background: #e9ecef;" class="flex-shrink-0">
                        @if($game->cover_img)
                            <img src="{{ asset($game->cover_img) }}" alt="{{ $game->title }} Cover" style="width: 100%; height: 100%; object-fit: cover;">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted small bg-secondary text-white">
                                <i class="bi bi-controller"></i>
                            </div>
                        @endif
                    </div>
                    
                    <div class="flex-grow-1 min-w-0">
                        <div class="fw-semibold text-truncate text-dark mb-0">
                            {{ $game->title }}
                        </div>
                        <div class="text-muted small text-truncate">
                            {{ $game->publisher ?? __('common.unknown') }}
                        </div>
                        <div class="text-muted small">
                            <span class="badge bg-warning text-dark px-2 py-1">
                                <i class="bi bi-star-fill me-1"></i> {{ $game->average_rating ?? '0.0' }}
                            </span>
                        </div>
                    </div>
                </a>
            @empty
                <div class="text-muted small text-center py-3">
                    {{ __('common.none') }}
                </div>
            @endforelse
        </div>
    </div>
</div>
