<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Game;
use App\Models\Post;

class DashboardController extends Controller
{
    public function index()
    {
        // Simple functional implementation fetching basic data for tiles
        $featuredGames = Game::with('genres')->inRandomOrder()->take(3)->get();
        $trendingGames = Game::withCount('users')->orderBy('users_count', 'desc')->take(5)->get();
        $newsFeed = Post::with('user', 'game')->latest()->take(10)->get();

        return view('dashboard', compact('featuredGames', 'trendingGames', 'newsFeed'));
    }
}
