<x-layout headtitle="Search Results">
    <div class="container-xl py-5" style="max-width: 1400px;">
        <h2 class="fw-bold mb-4" style="letter-spacing: -0.5px; color: #1a1d20;">Search Results for "{{ $query }}"</h2>

        @if(!$query)
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h5 class="mb-0">Please enter a search term to begin.</h5>
            </div>
        @elseif($games->isEmpty() && $users->isEmpty())
            <div class="alert alert-warning border-0 shadow-sm p-4 text-center">
                <h5 class="mb-0">No results found for "{{ $query }}". Try different keywords.</h5>
            </div>
        @else
            
            @if($games->isNotEmpty())
                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">🎮 Games</h3>
                        <span class="text-muted small">{{ $games->count() }} titles found</span>
                    </div>
                    <div class="row g-4">
                        @foreach($games as $game)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3" style="transition: transform 0.2s ease;">
                                <x-game-card :game="$game" />
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif

            @if($users->isNotEmpty())
                <hr class="my-5 opacity-10">

                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">👥 Users</h3>
                        <span class="text-muted small">{{ $users->count() }} users found</span>
                    </div>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush border-0">
                                @foreach($users as $user)
                                    <a href="/users/{{ $user->username }}" class="list-group-item list-group-item-action d-flex align-items-center py-4 border-bottom border-light">
                                        @if($user->avatar)
                                            <img src="{{ asset($user->avatar) }}" alt="{{ $user->username }}" class="rounded-circle me-3 object-fit-cover shadow-sm" width="60" height="60">
                                        @else
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-3 shadow-sm fw-bold" style="width: 60px; height: 60px; font-size: 1.5rem;">
                                                {{ strtoupper(substr($user->username, 0, 1)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <h5 class="mb-1 fw-bold text-dark">{{ $user->username }}</h5>
                                            <small class="text-muted">Joined {{ $user->created_at->format('M Y') }} • {{ $user->posts()->count() }} Posts</small>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif

        @endif
    </div>
</x-layout>
