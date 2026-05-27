<div class="col-md-6 col-lg-4 animate-fade-in">
    <div class="card shadow-sm border-0 h-100">
        <div class="card-body d-flex flex-column">
            <div class="mb-3">
                <h3 class="fw-bold mb-2">
                    {{ $playlist->name }}
                </h3>
                <p class="text-muted mb-0">
                    {{ $playlist->description ?? 'No description provided.' }}
                </p>
            </div>

            <div class="mt-auto d-flex justify-content-between align-items-center">
                <small class="text-muted">
                    {{ $playlist->games->count() }} games
                </small>

                <div class="d-flex gap-2">
                    @auth
                        @if($playlist->users->contains(auth()->id()))
                            <a href="/playlists/{{ $playlist->id }}/edit" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form action="/playlists/{{ $playlist->id }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        @endif
                    @endauth

                    <a href="/playlists/{{ $playlist->id }}" class="btn btn-primary btn-sm">
                        View
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>