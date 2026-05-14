<?php

namespace App\View\Components;

use App\Models\Game;
use Illuminate\View\Component;

class TrendingGames extends Component
{
    public $trendingGames;

    public function __construct()
    {
        // 1. Get the class name WITHOUT addslashes 
        // (PHP will resolve this to 'App\Models\Game' which SQLite reads perfectly)
        $gameModelClass = Game::class; 
        
        // 2. Generate the exact date string matching your local database timezone
        $sevenDaysAgo = now()->subDays(7)->format('Y-m-d H:i:s'); 

        $this->trendingGames = Game::select('games.*')
            ->selectRaw("
                (
                    -- 1. Date factor: Age penalty
                    (((julianday('now') - julianday(release_date)) * -1) / 10)
                    
                    +
                    
                    -- 2. Post count factor
                    (
                        (
                            SELECT COUNT(*) 
                            FROM posts 
                            WHERE posts.hub_type = '{$gameModelClass}' 
                            AND posts.hub_id = games.id 
                            AND (posts.created_at >= '{$sevenDaysAgo}' OR posts.updated_at >= '{$sevenDaysAgo}')
                        ) * 2
                    )
                    
                    +
                    
                    -- 3. Review rating factor
                    (COALESCE(
                        (
                            SELECT AVG(reviews.rating) 
                            FROM reviews 
                            INNER JOIN posts ON posts.id = reviews.post_id 
                            WHERE posts.hub_type = '{$gameModelClass}' 
                            AND posts.hub_id = games.id 
                            AND (reviews.updated_at >= '{$sevenDaysAgo}' OR reviews.created_at >= '{$sevenDaysAgo}')
                        ),
                    0) * 0.5)
                    
                ) AS trending_score
            ")
            ->where('release_date', '<=', now())
            ->orderByDesc('trending_score')
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('components.trending-games');
    }
}