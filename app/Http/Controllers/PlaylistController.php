<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Post;

class PlaylistController extends Controller
{
    public function show(Playlist $playlist)
    {
        // Retrieve root posts attached specifically to this morphable playlist hub
        $posts = Post::query()
            ->where('hub_type', 'playlist') // or \App\Models\Playlist::class if morph map isn't applied yet
            ->where('hub_id', $playlist->id)
            ->whereNull('parent_id')
            ->latest()
            ->paginate(10);

        return view('playlists.show', compact('playlist', 'posts'));
    }
}
