<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;

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
                $html = '';
                foreach ($games as $game) {
                    $html .= '<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">';
                    $html .= Blade::render('<x-game.card :game="$game" />', ['game' => $game]);
                    $html .= '</div>';
                }

                return response()->json([
                    'html' => $html,
                    'next_page_url' => $games->nextPageUrl(),
                ]);
            }

            // --- USERS AJAX ---
            if ($request->has('page_users')) {
                $html = '';
                foreach ($users as $user) {
                    $html .= '<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">';
                    $html .= Blade::render('<x-user.card :user="$user" layout="compact" />', ['user' => $user]);
                    $html .= '</div>';
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