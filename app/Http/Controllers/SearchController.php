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

        if (! $query) {
            return view('search.results', [
                'query' => $query,
                'games' => collect(),
                'users' => collect(),
            ]);
        }

        // Fix: Eager load relations and post counts to avoid N+1 on search results
        $games = Game::where('title', 'like', "%{$query}%")
            ->with(['genres', 'credits'])
            ->withCount('posts')
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
            if ($request->has('page_games')) {
                $html = '';
                foreach ($games as $game) {
                    $html .= view('games.partials.game-card-wrapper', compact('game'))->render();
                }

                return response()->json([
                    'html' => $html,
                    'next_page_url' => $games->nextPageUrl(),
                ]);
            }

            if ($request->has('page_users')) {
                $html = '';
                foreach ($users as $user) {
                    $html .= view('users.partials.compact-card-wrapper', compact('user'))->render();
                }

                return response()->json([
                    'html' => $html,
                    'next_page_url' => $users->nextPageUrl(),
                ]);
            }
        }

        return view('search.results', compact('games', 'users', 'query'));
    }
}
