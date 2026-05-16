@props(['game', 'playlists'])

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
            <hr class="mt-4 mb-3">
            <p> View relevant <a href="/games/{{ $game->id }}/hub"> Discussions</a></p>

        </div>
    </div>
</div>