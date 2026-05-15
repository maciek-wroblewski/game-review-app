<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{
    public function show(Playlist $playlist)
    {
        $playlist->load(['games.genres', 'users']);
        return view('playlists.show', compact('playlist'));
    }
}
