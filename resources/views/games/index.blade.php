<x-layout headtitle="{{ __('games.browse_games') }}">
    <div class="container-xl py-5">

        <section class="trending-section mb-5">
            <div class="trending-wrapper">
                <x-game.trending />
            </div>
        </section>

        <hr class="my-5 opacity-10">

        <section class="grid-section">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <h2 class="fw-bold mb-0">{{ __('games.explore') }}</h2>
                <div>
                <span class="text-muted small">{{ $games->total() }} {{ __('games.titles_found') }}</span>
                @if(auth()->check() && (auth()->user()->is_admin))
                    <a href="/games/create" class="btn btn-sm btn-outline-primary ms-3">
                        <i class="bi bi-plus-lg"></i> {{ __('games.add_game') }}
                    </a>
                @endif
                </div>
            </div>

            <div id="games-grid-wrapper" class="row g-4">
                @foreach($games as $game)
                    <div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                        <x-game.card :game="$game" />
                    </div>
                @endforeach
            </div>

            <x-load-more :paginator="$games" target="#games-grid-wrapper" />
            
        </section>
    </div>
</x-layout>