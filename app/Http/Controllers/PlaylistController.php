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

    public function edit(Playlist $playlist)
    {
        // Authorization: Only allow if authenticated user is associated with the playlist
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
            'is_public' => 'boolean',
        ]);

        $playlist->update($validated);

        return redirect("/playlists/{$playlist->id}")->with('success', 'Playlist updated!');
    }

    public function destroy(Playlist $playlist)
    {
        if (!$playlist->users->contains(auth()->id())) {
            abort(403);
        }

        $playlist->delete();

        return redirect('/users/' . auth()->id() . '/playlists')->with('success', 'Playlist deleted.');
    }
}
