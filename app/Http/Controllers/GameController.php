<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;

class GameController extends Controller
{
    public function show($slug)
    {
        $game = Game::where('slug', $slug)->with(['genres', 'developers', 'publishers', 'platforms'])->firstOrFail();
        
        // Fetch posts for this game's hub
        $posts = $game->posts()->with(['user', 'comments.user'])->latest()->get();

        // Calculate Average Rating
        $averageRating = $game->users()->avg('personal_rating');
        $averageRating = $averageRating ? round($averageRating, 1) : 'N/A';

        // Calculate Recommendations %
        $recommendations = $game->users()->whereNotNull('recommendation_rating')->get();
        $positiveCount = $recommendations->where('pivot.recommendation_rating', 'positive')->count();
        $totalRecs = $recommendations->count();
        $recommendationPercent = $totalRecs > 0 ? round(($positiveCount / $totalRecs) * 100) : 'N/A';

        return view('game.show', compact('game', 'posts', 'averageRating', 'recommendationPercent', 'totalRecs'));
    }
}
