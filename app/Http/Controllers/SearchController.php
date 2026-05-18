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

        $games = Game::where('title', 'like', "%{$query}%")
                     ->orderBy('title', 'asc')
                     ->get();

        $users = User::where('username', 'like', "%{$query}%")
                     ->orderBy('username', 'asc')
                     ->get();

        return view('search.results', compact('games', 'users', 'query'));
    }
}
