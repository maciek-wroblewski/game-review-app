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

        // Handle AJAX request
        if (request()->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => __('common.game_added_to_playlist', ['playlist' => $playlist->name])]);
        }

        return back()->with('success', __('common.game_added_to_playlist', ['playlist' => $playlist->name]));
    }

    public function destroy(Playlist $playlist, Game $game)
    {
        $playlist->games()->detach($game->id);

        // Handle AJAX request
        if (request()->wantsJson()) {
            return response()->json(['status' => 'success', 'message' => __('common.game_removed_from_playlist', ['playlist' => $playlist->name])]);
        }

        return back()->with('success', __('common.game_removed_from_playlist', ['playlist' => $playlist->name]));
    }
}