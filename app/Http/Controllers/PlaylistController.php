<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Post;
use Illuminate\Http\Request;

class PlaylistController extends Controller
{


    public function create()
    {
        return view('playlists.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        // Checkboxes only send data if checked
        $validated['is_public'] = $request->has('is_public');

        $playlist = Playlist::create($validated);
        
        // Attach the playlist to the currently authenticated user
        $playlist->users()->attach(auth()->id());

        return redirect("/playlists/{$playlist->id}")->with('success', 'Playlist created successfully!');
    }
    public function show(Request $request, Playlist $playlist)
    {
        // 1. Paginate games (custom page name: 'games_page')
        $games = $playlist->games()->paginate(12, ['*'], 'games_page');

        // 2. Paginate posts (custom page name: 'posts_page')
        $posts = Post::query()
            ->where('hub_type', 'playlist')
            ->where('hub_id', $playlist->id)
            ->whereNull('parent_id')
            ->latest()
            ->withFeedRelations() // Ensuring relationships load
            ->paginate(10, ['*'], 'posts_page');

        // Handle load-more AJAX responses
        if ($request->ajax()) {
            
            // If the request is for more games
            if ($request->has('games_page')) {
                $html = '';
                foreach ($games as $game) {
                    $html .= view('playlists.partials.game-card-wrapper', compact('game', 'playlist'))->render();
                }

                return response()->json([
                    'html' => $html,
                    'next_page_url' => $games->nextPageUrl(),
                ]);
            }

            // If the request is for more posts/comments
            if ($request->has('posts_page')) {
                return response()->json([
                    'html' => view('components.post.items', compact('posts'))->render(),
                    'next_page_url' => $posts->nextPageUrl(),
                ]);
            }
        }

        return view('playlists.show', compact('playlist', 'games', 'posts'));
    }

    public function edit(Playlist $playlist)
    {
        if (!$playlist->users->contains(auth()->id())) {
            abort(403);
        }

        return view('playlists.edit', compact('playlist'));
    }

    public function update(Request $request, Playlist $playlist)
    {
        if (!$playlist->users->contains(auth()->id())) {
            abort(403);
        } 

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $validated['is_public'] = $request->has('is_public');

        $playlist->update($validated);

        return redirect("/playlists/{$playlist->id}")->with('success', 'Playlist updated!');
    }
}