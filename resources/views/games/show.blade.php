<x-layout headtitle="{{ $game->title }}">
    <!-- Banner Section -->
    <div class="container-fluid p-0 mb-5" style="height: 400px; overflow: hidden; position: relative; background-color: #1a1a1a;">
        @if($game->banner_img)
            <img src="{{ $game->banner_img }}" alt="{{ $game->title }} Banner" class="w-100 h-100" style="object-fit: cover; opacity: 0.6;">
        @endif
        <div class="position-absolute bottom-0 start-0 p-5 w-100 d-flex align-items-end justify-content-between" style="background: linear-gradient(transparent, rgba(0,0,0,0.9));">
            <h1 class="display-3 fw-bold text-white mb-0">{{ $game->title }}</h1>
            @if($averageRating)
                <div class="bg-warning text-dark rounded-circle d-flex align-items-center justify-content-center shadow-lg" style="width: 100px; height: 100px; border: 4px solid white;">
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
            <div class="col-md-4 mb-4">
                <div class="card shadow-sm border-0">
                    @if($game->cover_img)
                        <img src="{{ $game->cover_img }}" class="card-img-top" alt="{{ $game->title }} Cover">
                    @endif
                    <div class="card-body">
                        <h5 class="card-title fw-bold border-bottom pb-2 mb-3">Game Info</h5>
                        <p class="mb-2">
                            <strong>Release Date:</strong> <br>
                            {{ $game->release_date ? $game->release_date->format('M d, Y') : 'Unknown' }}
                        </p>
                        
                        <div class="mb-3">
                            <strong>Developers & Credits:</strong> <br>
                            @forelse($game->credits as $credit)
                                <div class="text-muted small mb-1">
                                    <span class="fw-bold">{{ $credit->username ?? $credit->name }}</span>
                                    <span class="badge bg-secondary ms-1">{{ $credit->pivot->role }}</span>
                                </div>
                            @empty
                                <span class="text-muted small">None specified</span>
                            @endforelse
                        </div>

                        <div class="mb-3">
                            <strong>Genres:</strong> <br>
                            @forelse($game->genres as $genre)
                                <span class="badge bg-primary">{{ $genre->name }}</span>
                            @empty
                                <span class="text-muted small">None specified</span>
                            @endforelse
                        </div>

                        <p class="card-text text-muted">{{ $game->details }}</p>

                        <!-- Add to Playlist -->
                        @if($playlists->count() > 0)
                            <hr class="mt-4 mb-3">
                            <form action="/playlists/{{ $playlists->first()->id }}/games/{{ $game->id }}" method="POST" class="d-flex align-items-center gap-2 mt-2" onsubmit="this.action='/playlists/'+document.getElementById('playlist_select').value+'/games/{{ $game->id }}';">
                                @csrf
                                <select id="playlist_select" class="form-select form-select-sm border-0 bg-light" required>
                                    @foreach($playlists as $list)
                                        <option value="{{ $list->id }}">{{ $list->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary text-nowrap fw-bold shadow-sm">+ Add to List</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Right Column: Reviews -->
            <div class="col-md-8">
                <!-- Write a Review Form -->
                <div class="card shadow-sm mb-5 border-0 bg-light">
                    <div class="card-body p-4">
                        <h4 class="mb-3 fw-bold">Leave a Review</h4>
                        
                        @if ($errors->any())
                            <div class="alert alert-danger border-0">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <form action="/games/{{ $game->id }}/reviews" method="POST">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="rating" class="form-label fw-semibold">Rating (1-10)</label>
                                    <select class="form-select border-0 shadow-sm" id="rating" name="rating" required>
                                        <option value="">Choose...</option>
                                        @for ($i = 1; $i <= 10; $i++)
                                            <option value="{{ $i }}" {{ old('rating') == $i ? 'selected' : '' }}>{{ $i }} / 10</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="body" class="form-label fw-semibold">Your Thoughts</label>
                                <textarea class="form-control border-0 shadow-sm" id="body" name="body" rows="4" required placeholder="What did you think of the game?">{{ old('body') }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-primary fw-bold px-4 py-2">Publish Review</button>
                        </form>
                    </div>
                </div>

                <!-- Existing Reviews List -->
                <h3 class="mb-4 fw-bold">Player Reviews <span class="text-muted fs-5 fw-normal">({{ $game->posts->count() }})</span></h3>
                
                @forelse($game->posts as $post)
                    <div class="card shadow-sm mb-4 border-0">
                        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                            <span class="fw-bold text-primary">{{ $post->author->username ?? $post->author->name ?? 'Anonymous User' }}</span>
                            <span class="badge rounded-pill {{ $post->review->rating >= 7 ? 'bg-success' : ($post->review->rating >= 4 ? 'bg-warning text-dark' : 'bg-danger') }} fs-6 px-3 py-2">
                                {{ $post->review->rating }} / 10
                            </span>
                        </div>
                        <div class="card-body">
                            <p class="card-text fs-5" style="white-space: pre-line;">{{ $post->body }}</p>
                        </div>
                        <div class="card-footer text-muted small bg-white border-0 pb-3 d-flex justify-content-between align-items-center">
                            <span>Posted on {{ $post->created_at->format('M d, Y') }}</span>
                            <form action="/posts/{{ $post->id }}/like" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="btn btn-sm btn-light border shadow-sm">
                                    👍 Helpful 
                                    @if($post->likes->count() > 0)
                                        <span class="badge bg-secondary ms-1">{{ $post->likes->count() }}</span>
                                    @endif
                                </button>
                            </form>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                        <h5 class="mb-0">No reviews yet! Be the first to share your thoughts above.</h5>
                    </div>
                @endforelse

            </div>
        </div>
    </div>
</x-layout>
