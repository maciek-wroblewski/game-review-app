<?php

namespace App\Http\Controllers;

use App\Models\Game;
use Illuminate\Http\Request;

class GameController extends Controller
{
    /**
     * Display the specified game.
     */
    public function show(Game $game)
    {
        // Eager load the game's genres, credits, and its posts (specifically those that are reviews),
        // including the review details, the author of the post, and likes.
        $game->load([
            'genres',
            'credits',
            'posts' => function ($query) {
                $query->whereHas('review')
                      ->with(['author', 'review', 'likes'])
                      ->latest(); // Show newest reviews first
            }
        ]);

        // Calculate the average rating from the loaded reviews
        // We filter posts to only those that have a review relation loaded
        $reviews = $game->posts->filter(fn($post) => $post->review);
        $averageRating = $reviews->count() > 0 ? round($reviews->avg('review.rating'), 1) : null;

        // Fetch the current user's playlists (default to User ID 1 for testing)
        $user = \App\Models\User::find(auth()->id() ?? 1);
        $playlists = $user ? $user->playlists : collect();

        return view('games.show', compact('game', 'averageRating', 'playlists'));
    }
}
