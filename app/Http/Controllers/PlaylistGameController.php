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
        // Check if the game is already in the playlist to avoid duplicates
        if (!$playlist->games()->where('game_id', $game->id)->exists()) {
            
            // Get the current max order in the playlist so we can append to the end
            $maxOrder = $playlist->games()->max('order') ?? 0;
            
            // Attach the game to the playlist via the pivot table
            $playlist->games()->attach($game->id, ['order' => $maxOrder + 1]);
        }

        // Redirect back to the game page with a success message
        return back()->with('success', "Game added to {$playlist->name} successfully!");
    }
}
