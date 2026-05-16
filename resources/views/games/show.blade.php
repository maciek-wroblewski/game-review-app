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
                        <form id="playlist_form" method="POST" class="d-flex align-items-center gap-2 mt-2"
                            action="/playlists/{{ $playlists->first()->id }}/games/{{ $game->id }}">
                            @csrf
                            <input type="hidden" name="_method" id="form_method" value="POST">
                            <select id="playlist_select" class="form-select form-select-sm border-0 bg-light" required
                                onchange="updatePlaylistButtonState()">
                                @foreach($playlists as $list)
                                <option value="{{ $list->id }}"
                                    data-in-list="{{ $list->games->contains($game->id) ? 'true' : 'false' }}">{{
                                    $list->name }}</option>
                                @endforeach
                            </select>
                            <button id="playlist_submit_btn" type="submit"
                                class="btn btn-sm btn-outline-primary text-nowrap fw-bold shadow-sm">+ Add to
                                List</button>
                        </form>

                        <script>
                            function updatePlaylistButtonState() {
                                const select = document.getElementById('playlist_select');
                                const option = select.options[select.selectedIndex];
                                const form = document.getElementById('playlist_form');
                                const methodInput = document.getElementById('form_method');
                                const btn = document.getElementById('playlist_submit_btn');
                                
                                form.action = '/playlists/' + option.value + '/games/{{ $game->id }}';
                                
                                if (option.getAttribute('data-in-list') === 'true') {
                                    btn.textContent = '- Remove from List';
                                    btn.className = 'btn btn-sm btn-outline-danger text-nowrap fw-bold shadow-sm';
                                    methodInput.value = 'DELETE';
                                } else {
                                    btn.textContent = '+ Add to List';
                                    btn.className = 'btn btn-sm btn-outline-primary text-nowrap fw-bold shadow-sm';
                                    methodInput.value = 'POST';
                                }
                            }
                            // Run on page load
                            document.addEventListener('DOMContentLoaded', updatePlaylistButtonState);
                        </script>
                        @endif
                    </div>
                </div>
            </div>

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