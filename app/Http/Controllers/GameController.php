<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Game;

class GameController extends Controller

{
    public function index()
    {
        $games = Game::with(['genres', 'credits' => function($query) {
            $query->withPivot('role');
        }])
        ->withCount('posts')
        ->orderBy('average_rating', 'desc')
        ->paginate(12);
        return view('Games.index', compact('games'));
    }
}
