<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistGameController extends Controller
{
    /**
     * Add a game to a playlist.
     */
    public function store(Playlist $playlist, Game $game)
    {
        if (!$playlist->games()->where('game_id', $game->id)->exists()) {
            
            $maxOrder = $playlist->games()->max('order') ?? 0;
            
            $playlist->games()->attach($game->id, ['order' => $maxOrder + 1]);
        }

        return back()->with('success', "Game added to {$playlist->name} successfully!");
    }

    public function destroy(Playlist $playlist, Game $game)
    {
        $playlist->games()->detach($game->id);

        return back()->with('success', "Game removed from {$playlist->name} successfully!");
    }
}
