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
                <h2 class="fw-bold mb-0">🎮 {{ __('games.explore') }}</h2>
                <span class="text-muted small">{{ $games->total() }} {{ __('games.titles_found') }}</span>
            </div>

            <div id="games-grid-wrapper" class="row g-4">
                @foreach($games as $game)
                    @include('games.partials.game-card-wrapper', ['game' => $game])
                @endforeach
            </div>

            <x-load-more :paginator="$games" target="#games-grid-wrapper" />
            
        </section>
    </div>
</x-layout>