<?php

namespace App\View\Components;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;
use App\Models\Game;

class GameCard extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct(public Game $game)
    {
        // Safety Optimization: If the calling controller forgot to eager-load,
        // load missing relations safely to minimize redundant query fragmentation.
        $this->game->loadMissing(['genres', 'credits']);
        
        if (!isset($this->game->reviews_count)) {
            $this->game->loadCount('reviews');
        }
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        return view('components.game.card');
    }
}