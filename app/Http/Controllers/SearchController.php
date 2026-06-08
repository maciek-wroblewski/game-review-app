<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use App\Http\Controllers\Concerns\HasPaginatedResponses;

class SearchController extends Controller
{
    use HasPaginatedResponses;
    public function index(Request $request)
    {
        $query = $request->input('q');

        if (! $query) {
            return view('search.results', [
                'query' => $query,
                'games' => collect(),
                'users' => collect(),
            ]);
        }

        $games = Game::where('title', 'like', "%{$query}%")
            ->with(['genres', 'credits'])
            ->withCount('posts')
            ->orderBy('title', 'asc')
            ->paginate(12, ['*'], 'page_games')
            ->appends(['q' => $query]);

        $users = User::where('username', 'like', "%{$query}%")
            ->withCompactCounts()
            ->orderBy('username', 'asc')
            ->paginate(10, ['*'], 'page_users')
            ->appends(['q' => $query]);

        if ($request->ajax()) {
            // --- GAMES AJAX ---
            if ($request->has('page_games')) {
                return $this->ajaxCardGrid($games, 'components.game.card', 'game');
            }

            // --- USERS AJAX ---
            if ($request->has('page_users')) {
                return $this->ajaxCardGrid($users, 'components.user.card', 'user', ['layout' => 'compact']);
            }
        }

        return view('search.results', compact('games', 'users', 'query'));
    }
}