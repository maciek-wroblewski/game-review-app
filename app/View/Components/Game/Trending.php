<?php

namespace App\View\Components\Game;

use App\Models\Game;
use Illuminate\View\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

class Trending extends Component
{
    public Collection $trendingGames;

    public function __construct($games = null)
    {
        $this->trendingGames = $games ?? self::getTrendingGames();
    }

    /**
     * Get the games with scores, handling cache logic.
     */
    public static function getTrendingGames(): Collection
    {
        return Cache::remember('trending_games_v3_records', now()->addDay(), function () {
            $scores = self::calculateTrendingScores();

            if (empty($scores)) {
                return collect();
            }

            // Fetch fresh models for these IDs
            $gameIds = array_keys($scores);
            
            return Game::whereIn('id', $gameIds)
                ->with(['genres', 'credits:id,username'])
                ->withCount('reviews')
                ->get()
                ->map(function ($game) use ($scores) {
                    // Attach the score from the cache to the fresh model
                    $game->trending_score = $scores[$game->id] ?? 0;
                    return $game;
                })
                // Since whereIn doesn't preserve order, we sort here
                ->sortByDesc('trending_score')
                ->values();
        });
    }

    /**
     * The "Heavy Lifter" - Returns a simple [id => score] array.
     */
    public static function calculateTrendingScores(): array
    {
        $gameModelClass = (new Game)->getMorphClass();
        $sevenDaysAgo = now()->subDays(7)->toDateTimeString();

        $data = Game::select('id')
            ->selectRaw("
                (
                    ((julianday('now') - julianday(release_date)) * -1) / 10.0
                ) + (
                    SELECT COUNT(*) 
                    FROM posts 
                    WHERE posts.hub_type = ? 
                    AND posts.hub_id = games.id 
                    AND (posts.created_at >= ? OR posts.updated_at >= ?)
                ) * 2.0 + (
                    COALESCE((
                        SELECT AVG(reviews.rating) 
                        FROM reviews 
                        INNER JOIN posts ON posts.id = reviews.post_id 
                        WHERE posts.hub_type = ? 
                        AND posts.hub_id = games.id 
                        AND (reviews.updated_at >= ? OR reviews.created_at >= ?)
                    ), 0) * 0.5
                ) AS score", 
                [$gameModelClass, $sevenDaysAgo, $sevenDaysAgo, $gameModelClass, $sevenDaysAgo, $sevenDaysAgo]
            )
            ->where('release_date', '<=', now())
            ->orderByDesc('score')
            ->take(10)
            ->get();

        return $data->pluck('score', 'id')->toArray();
    }

    public function render()
    {
        return view('components.game.trending');
    }
}