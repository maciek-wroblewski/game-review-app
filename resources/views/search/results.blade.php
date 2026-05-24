<x-layout headtitle="{{ __('Search Results') }}">
    <div class="container-xl py-5" style="max-width: 1400px;">
        <h2 class="fw-bold mb-4" style="letter-spacing: -0.5px; color: #1a1d20;">{{ __('Search Results for ":query"', ['query' => $query]) }}</h2>

        @if(!$query)
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h5 class="mb-0">{{ __('Please enter a search term to begin.') }}</h5>
            </div>
        @elseif($games->isEmpty() && $users->isEmpty())
            <div class="alert alert-warning border-0 shadow-sm p-4 text-center">
                <h5 class="mb-0">{{ __('No results found for ":query". Try different keywords.', ['query' => $query]) }}</h5>
            </div>
        @else
            
            @if($games->isNotEmpty())
                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">🎮 {{ __('Games') }}</h3>
                        <span class="text-muted small">{{ $games->total() }} {{ __('titles found') }}</span>
                    </div>
                    
                    <div class="row g-4" id="games-grid">
                        @foreach($games as $game)
                            @include('games.partials.game-card-wrapper', ['game' => $game])
                        @endforeach
                    </div>

                    <x-load-more :paginator="$games" target="#games-grid" :text="__('Load More Games')" />
                </section>
            @endif

            @if($users->isNotEmpty())
                <hr class="my-5 opacity-10">

                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">👥 {{ __('Users') }}</h3>
                        <span class="text-muted small">{{ $users->total() }} {{ __('users found') }}</span>
                    </div>
                    
                    <div class="row g-4" id="users-grid">
                        @foreach($users as $user)
                            @include('users.partials.compact-card-wrapper', ['user' => $user])
                        @endforeach
                    </div>

                    <x-load-more :paginator="$users" target="#users-grid" :text="__('Load More Users')" />
                </section>
            @endif

        @endif
    </div>
</x-layout>