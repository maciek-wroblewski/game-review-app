<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;


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
            'cover' => 'nullable|image|max:2048', // <-- Validate as image (max 2MB)
        ]);

        $validated['is_public'] = $request->has('is_public');

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('playlist-covers', 'public');
        }

        $playlist = Playlist::create($validated);
        $playlist->users()->attach(auth()->id());
        Log::info('Created playlist: '.$playlist->name.' (ID: '.$playlist->id.') by '.(auth()->check() ? auth()->user()->username : 'guest'));
        return redirect("/playlists/{$playlist->id}")->with('success', 'Playlist created successfully!');
    }

    public function show(Request $request, Playlist $playlist)
    {
        $playlist->load('users')->loadCount('games');

        $games = $playlist->games()
            ->with(['genres', 'credits'])
            ->paginate(12, ['*'], 'games_page');

        $posts = Post::query()
            ->where('hub_type', 'playlist')
            ->where('hub_id', $playlist->id)
            ->whereNull('parent_id')
            ->latest()
            ->withFeedRelations() 
            ->paginate(10, ['*'], 'posts_page');

        if ($request->ajax()) {

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

            if ($request->has('posts_page')) {
                return response()->json([
                    'html' => view('components.post.items', compact('posts'))->render(),
                    'next_page_url' => $posts->nextPageUrl(),
                ]);
            }
        }

        Log::info('Viewing playlist: '.$playlist->name.' (ID: '.$playlist->id.') by '.(Auth::check() ? Auth::user()->username : 'guest'));
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
            'cover' => 'nullable|image|max:2048', // <-- Validation
        ]);

        $validated['is_public'] = $request->has('is_public');

        if ($request->hasFile('cover')) {
            if ($playlist->cover) {
                Storage::disk('public')->delete($playlist->cover);
            }
            $validated['cover'] = $request->file('cover')->store('playlist-covers', 'public');
        }

        $playlist->update($validated);

        return redirect("/playlists/{$playlist->id}")->with('success', 'Playlist updated!');
    }

    public function destroy(Playlist $playlist)
    {
        if (!$playlist->users->contains(auth()->id())) {
            abort(403);
        }

        if ($playlist->cover) {
            Storage::disk('public')->delete($playlist->cover);
        }

        $playlist->delete();

        return redirect('/users/' . auth()->id() . '/playlists')->with('success', 'Playlist deleted.');
    }
}
