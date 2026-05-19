<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->input('q');

        if (!$query) {
            return view('search.results', [
                'query' => $query,
                'games' => collect(),
                'users' => collect(),
            ]);
        }

        // Paginate games independently using 'page_games'
        $games = Game::where('title', 'like', "%{$query}%")
                     ->orderBy('title', 'asc')
                     ->paginate(12, ['*'], 'page_games')
                     ->appends(['q' => $query]);

        // Paginate users independently using 'page_users'
        $users = User::where('username', 'like', "%{$query}%")
                     ->orderBy('username', 'asc')
                     ->paginate(10, ['*'], 'page_users')
                     ->appends(['q' => $query]);

        // AJAX Optimization Framework for Infinite Scroll Elements
        if ($request->ajax()) {
            // Check if the AJAX fetch call belongs to the games pagination tracker
            if ($request->has('page_games')) {
                return view('games.partials.game-card-wrapper', compact('games'))->render();
            }
            // Check if the AJAX fetch call belongs to the users pagination tracker
            if ($request->has('page_users')) {
                return view('users.partials.follower-card-wrapper', compact('users'))->render();
            }
        }

        return view('search.results', compact('games', 'users', 'query'));
    }
}
