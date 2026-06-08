<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use App\Http\Controllers\Concerns\HasPaginatedResponses;

class PlaylistController extends Controller
{
    use HasPaginatedResponses;

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

        $validated['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('cover')) {
            $validated['cover'] = $request->file('cover')->store('playlist-covers', 'public');
        }

        $playlist = Playlist::create($validated);
    
        $users = $request->input('users', []);
        if (!in_array(auth()->id(), $users)) {
            $users[] = auth()->id();
        }
        $playlist->users()->attach($users);
        Log::info('Created playlist: '.$playlist->name.' (ID: '.$playlist->id.') by '.(auth()->check() ? auth()->user()->username : 'guest'));
        return redirect("/playlists/{$playlist->id}")->with('success', __('common.playlist_created'));
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
            ->withFeedRelations(['hub' => false, 'review' => false]) 
            ->paginate(10, ['*'], 'posts_page');

        $posts->getCollection()->each(function ($post) use ($playlist) {
            $post->setRelation('hub', $playlist);
            $post->setRelation('review', null);

            if ($post->relationLoaded('parent') && $post->parent) {
                $post->parent->setRelation('hub', $playlist);
                $post->parent->setRelation('review', null);
            }
        });

        if ($request->ajax()) {
            if ($request->has('games_page')) {
                return $this->ajaxCardGrid($games, 'components.game.card', 'game', ['playlist' => $playlist]);
            }

            if ($request->has('posts_page')) {
                return $this->ajaxFeed($posts);
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

        $validated['is_public'] = $request->boolean('is_public');

        if ($request->hasFile('cover')) {
            if ($playlist->cover) {
                Storage::disk('public')->delete($playlist->cover);
            }
            $validated['cover'] = $request->file('cover')->store('playlist-covers', 'public');
        }

        $playlist->update($validated);

        if ($request->has('users')) {
            $users = $request->input('users');
            if (!in_array(auth()->id(), $users)) {
                $users[] = auth()->id();
            }
            $playlist->users()->sync($users);
        }
        Log::info('Updated playlist: '.$playlist->name.' (ID: '.$playlist->id.') by '.(auth()->check() ? auth()->user()->username : 'guest'));
        return redirect("/playlists/{$playlist->id}")->with('success', __('common.playlist_updated'));
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
        Log::info('Deleted playlist: '.$playlist->name.' (ID: '.$playlist->id.') by '.(auth()->check() ? auth()->user()->username : 'guest'));
        return redirect('/users/' . auth()->id() . '/playlists')->with('success', __('common.playlist_deleted'));
    }
}
