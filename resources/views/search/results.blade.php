<x-layout headtitle="{{ __('common.search_results') }}">
    <div class="container-xl py-5" style="max-width: 1400px;">
        <h2 class="fw-bold mb-4" style="letter-spacing: -0.5px; color: #1a1d20;">{{ __('common.search_results_for', ['query' => $query]) }}</h2>

        @if(!$query)
            <div class="alert alert-info border-0 shadow-sm p-4 text-center">
                <h5 class="mb-0">{{ __('common.please_enter_search_term') }}</h5>
            </div>
        @elseif($games->isEmpty() && $users->isEmpty())
            <div class="alert alert-warning border-0 shadow-sm p-4 text-center">
                <h5 class="mb-0">{{ __('common.no_results_found', ['query' => $query]) }}</h5>
            </div>
        @else
            
            @if($games->isNotEmpty())
                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">🎮 {{ __('common.games') }}</h3>
                        <span class="text-muted small">{{ $games->total() }} {{ __('games.titles_found') }}</span>
                    </div>
                    
                    <div class="row g-4" id="games-grid">
                        @foreach($games as $game)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                                <x-game.card :game="$game" />
                            </div>
                        @endforeach
                    </div>

                    <x-load-more :paginator="$games" target="#games-grid" :text="__('common.load_more_games')" />
                </section>
            @endif

            @if($users->isNotEmpty())
                <hr class="my-5 opacity-10">

                <section class="mb-5">
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <h3 class="fw-bold mb-0">👥 {{ __('common.users') }}</h3>
                        <span class="text-muted small">{{ $users->total() }} {{ __('common.users_found') }}</span>
                    </div>
                    
                    <div class="row g-4" id="users-grid">
                        @foreach($users as $user)
                            <div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                                <x-user.card :user="$user" layout="compact" />
                            </div>
                        @endforeach
                    </div>

                    <x-load-more :paginator="$users" target="#users-grid" :text="__('common.load_more_users')" />
                </section>
            @endif

        @endif
    </div>
</x-layout>