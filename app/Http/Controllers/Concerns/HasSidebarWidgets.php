<?php

namespace App\Http\Controllers\Concerns;

use App\Models\Game;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use App\View\Components\Game\Trending;

trait HasSidebarWidgets
{
    /**
     * Get the cached global sidebar widgets data.
     * Combines multiple database/cache checks into a single cache hit.
     */
    protected function getSidebarWidgetsData(): array
    {
        return Cache::remember('global_sidebar_widgets', 3600, function () {
            $topGames = Game::query()
                ->withCount(['posts as reviews_count' => function ($query) {
                    $query->has('review');
                }])
                ->orderBy('average_rating', 'desc')
                ->take(5)
                ->get();

            $activeUsers = User::query()
                ->with('avatar')
                ->withCount(['posts', 'followers', 'following', 'reviews'])
                ->orderBy('followers_count', 'desc')
                ->orderBy('posts_count', 'desc')
                ->take(5)
                ->get();

            // Fetch trending games collection
            $trendingGames = Trending::getTrendingGames();

            return [
                'top_games' => $topGames,
                'active_users' => $activeUsers,
                'trending_games' => $trendingGames,
            ];
        });
    }
}
