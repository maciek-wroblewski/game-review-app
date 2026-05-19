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
                        <span class="text-muted small">{{ $games->total() }} titles found</span>
                    </div>
                    
                    <div class="row g-4" id="games-grid">
                        @foreach($games as $game)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3" style="transition: transform 0.2s ease;">
                                <x-game-card :game="$game" />
                            </div>
                        @endforeach
                    </div>

                    <x-load-more :paginator="$games" target="#games-grid" text="Load More Games" />
                </section>
            @endif

            @if($users->isNotEmpty())
                <hr class="my-5 opacity-10">

                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">👥 Users</h3>
                        <span class="text-muted small">{{ $users->total() }} users found</span>
                    </div>
                    
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="list-group list-group-flush border-0 d-flex flex-column row-gap-3 p-3" id="users-list">
                                @foreach($users as $user)
                                <x-clickable-card link='/users/{{ $user->username }}'>
                                    <div class="card shadow-sm border-0 d-flex flex-row column-gap-3 p-3 align-items-center justify-content-between">
                                        <div class="d-flex flex-row column-gap-3 align-items-center">
                                            <x-user.avatar :user='$user' size='60px'/>
                                            <div>
                                                <h5 class="mb-1 fw-bold text-dark">{{ $user->username }}</h5>
                                                <small class="text-muted">Joined {{ $user->created_at->format('M Y') }} • {{ $user->posts()->count() }} Posts</small>
                                            </div>
                                        </div>
                                        <x-follow-button :targetUser='$user'/>
                                    </div>
                                </x-clickable-card>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <x-load-more :paginator="$users" target="#users-list" text="Load More Users" />
                </section>
            @endif

        @endif
    </div>
</x-layout>