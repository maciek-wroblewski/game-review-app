@props(['game', 'playlists'])

<div class="col-md-4 mb-4">
    <div class="card shadow-sm border-0">
        @if($game->cover_img)
        <img src="{{ asset($game->cover_img) }}" class="card-img-top" style="aspect-ratio: 2/3; object-fit: cover; background: #333;" alt="{{ $game->title }} Cover">
        @endif
        <div class="card-body">
            
            {{-- Header with Edit Button --}}
            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h5 class="card-title fw-bold mb-0">{{ __('games.info') }}</h5>
                
                @if(auth()->check() && (auth()->user()->is_admin || $game->credits->contains('id', auth()->id())))
                    <a href="/games/{{ $game->id }}/edit" class="btn btn-sm btn-outline-secondary">
                        <i class="bi bi-pencil"></i> Edit
                    </a>
                @endif
            </div>

            <p class="mb-2">
                <strong>{{ __('games.release_date') }}:</strong> <br>
                {{ $game->release_date ? $game->release_date->format('M d, Y') : 'Unknown' }}
            </p>

            @if($game->publisher)
            <p class="mb-2">
                <strong>{{ __('games.publisher') }}:</strong> <br>
                {{ $game->publisher }}
            </p>
            @endif

            <div class="mb-3">
                <strong>{{ __('games.developer') }} & {{ __('games.credits') }}:</strong> <br>
                @forelse($game->credits as $credit)
                <div class="text-muted small mb-1">
                    <span class="fw-bold">{{ $credit->username ?? $credit->name }}</span>
                    <span class="badge bg-secondary ms-1">{{ $credit->pivot->role }}</span>
                </div>
                @empty
                <span class="text-muted small">{{ __('games.not_specified') }}</span>
                @endforelse
            </div>

            <div class="mb-3">
                <strong>{{ __('games.genre') }}:</strong> <br>
                @forelse($game->genres as $genre)
                <span class="badge bg-primary">{{ $genre->name }}</span>
                @empty
                <span class="text-muted small">{{ __('games.not_specified') }}</span>
                @endforelse
            </div>

            <div class="card-text text-muted">
                {!! Str::markdown($game->details ?? '') !!}
            </div>

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
                        data-in-list="{{ $list->games->contains($game->id) ? 'true' : 'false' }}">{{ $list->name }}
                    </option>
                    @endforeach
                </select>
                <button id="playlist_submit_btn" type="submit"
                    class="btn btn-sm btn-outline-primary text-nowrap fw-bold shadow-sm">{{ __('games.add_to_list') }}</button>
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
                        btn.textContent = '{{ __('games.remove_from_list') }}';
                        btn.className = 'btn btn-sm btn-outline-danger text-nowrap fw-bold shadow-sm';
                        methodInput.value = 'DELETE';
                    } else {
                        btn.textContent = '{{ __('games.add_to_list') }}';
                        btn.className = 'btn btn-sm btn-outline-primary text-nowrap fw-bold shadow-sm';
                        methodInput.value = 'POST';
                    }
                }

                document.addEventListener('DOMContentLoaded', () => {
                    // Initial update on load
                    updatePlaylistButtonState();

                    // Hijack form submission for AJAX
                    const form = document.getElementById('playlist_form');
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                        
                        const btn = document.getElementById('playlist_submit_btn');
                        btn.disabled = true;
                        
                        const originalBtnContent = btn.innerHTML;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';

                        const method = document.getElementById('form_method').value;

                        fetch(form.action, {
                            method: method === 'DELETE' ? 'DELETE' : 'POST',
                            headers: {
                                'X-CSRF-TOKEN': form.querySelector('input[name="_token"]').value,
                                'Accept': 'application/json',
                            }
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok.');
                            return response.json();
                        })
                        .then(data => {
                            const select = document.getElementById('playlist_select');
                            const option = select.options[select.selectedIndex];
                            
                            // Swap data-in-list boolean
                            if (option.getAttribute('data-in-list') === 'true') {
                                option.setAttribute('data-in-list', 'false');
                            } else {
                                option.setAttribute('data-in-list', 'true');
                            }
                            
                            // Re-run state function to instantly visually change the button styling
                            updatePlaylistButtonState();
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            btn.innerHTML = 'Error!';
                            setTimeout(() => updatePlaylistButtonState(), 2000);
                        })
                        .finally(() => {
                            btn.disabled = false;
                        });
                    });
                });
            </script>
            @endif
        </div>
    </div>
</div>