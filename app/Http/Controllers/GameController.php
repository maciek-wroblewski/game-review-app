<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class GameController extends Controller
{
    public function index(Request $request)
    {
        $games = Game::with(['genres', 'credits' => function ($query) {
            $query->withPivot('role');
        }])
            ->withCount('posts')
            ->orderBy('average_rating', 'desc')
            ->paginate(12);

        if ($request->ajax()) {
            $html = '';
            foreach ($games as $game) {
                $html .= view('games.partials.game-card-wrapper', compact('game'))->render();
            }

            return response()->json([
                'html' => $html,
                'next_page_url' => $games->nextPageUrl(),
            ]);
        }

        return view('games.index', compact('games'));
    }

    public function show(Request $request, Game $game)
    {
        $game->load(['genres', 'credits']);
        $game->loadCount(['posts' => function ($query) {
            $query->whereHas('review');
        }]);

        $userId = auth()->id() ?? null;
        $user = auth()->user();
        $playlists = $user
            ? $user->playlists()->with(['games' => function ($query) use ($game) {
                $query->where('games.id', $game->id);
            }])->get()
            : collect();

        $userReviewPost = $userId
                    ? $game->posts()
                        ->where('user_id', $userId)
                        ->has('review')
                        ->withFeedRelations()
                        ->first()
                    : null;

        $posts = $game->posts()
            ->has('review')
            ->withFeedRelations()
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', '!=', $userId);
            })
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        if ($request->ajax()) {
            return view('components.post.items', compact('posts'))->render();
        }

        return view('games.show', compact('game', 'playlists', 'userReviewPost', 'posts'));
    }

    public function loadMore(Request $request)
    {
        $page = $request->get('page', 1);

        $games = Game::with(['genres', 'credits' => function ($query) {
            $query->withPivot('role');
        }])
            ->withCount('posts')
            ->orderBy('average_rating', 'desc')
            ->paginate(12, ['*'], 'page', $page);

        $html = '';

        foreach ($games as $game) {
            $html .= view('games.partials.game-card-wrapper', ['game' => $game])->render();
        }

        return response()->json([
            'html' => $html,
            'hasMore' => $games->hasMorePages(),
            'nextPage' => $games->currentPage() + 1,
        ]);
    }

    public function discussions(Request $request, Game $game)
    {
        $game->load(['genres', 'credits']);
        $game->loadCount(['posts' => function ($query) {
            $query->doesntHave('review');
        }]);

        $userId = auth()->id() ?? null;
        $user = auth()->user();
        
        $playlists = $user
            ? $user->playlists()->with(['games' => function ($query) use ($game) {
                $query->where('games.id', $game->id);
            }])->get()
            : collect();

        // 1. Fetch posts, turning off the redundant eager loads
        $posts = $game->posts()
            ->doesntHave('review')
            ->withFeedRelations(['hub' => false, 'review' => false]) // <-- Turn off hub and review
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        // 2. Manually map the data to prevent N+1 fallbacks
        $posts->getCollection()->each(function ($post) use ($game) {
            // Attach the hub we already fetched
            $post->setRelation('hub', $game);
            
            // Tell Laravel there is no review to block N+1 on $post->isReview()
            $post->setRelation('review', null); 

            // If this post is a reply, the parent also shares the same hub
            if ($post->relationLoaded('parent') && $post->parent) {
                $post->parent->setRelation('hub', $game);
                
                // Assuming parent posts in this view also aren't reviews
                $post->parent->setRelation('review', null);
            }
        });

        if ($request->ajax()) {
            return view('components.post.items', compact('posts'))->render();
        }

        return view('games.discussions', compact('game', 'playlists', 'posts'));
    }

    public function edit(Game $game)
    {
        if (!auth()->check() || (!auth()->user()->is_admin && !$game->credits->contains('id', auth()->id()))) {
            abort(403, __('You do not have permission to edit this game.'));
        }

        $genres = Genre::orderBy('name')->get(); // Added genres fetch

        return view('games.edit', compact('game', 'genres')); // Passed to view
    }

    public function update(Request $request, Game $game)
    {
        if (!auth()->check() || (!auth()->user()->is_admin && !$game->credits->contains('id', auth()->id()))) {
            abort(403, __('You do not have permission to edit this game.'));
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'details' => 'nullable|string',
            'banner_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,webp|max:1024',
            'genres' => 'nullable|array', // Removed the strict ID exists check
        ]);

        // Process file uploads
        if ($request->hasFile('banner_img')) {
            $path = $request->file('banner_img')->store('games/banners', 'public');
            $validated['banner_img'] = '/storage/' . $path;
        }

        if ($request->hasFile('cover_img')) {
            $path = $request->file('cover_img')->store('games/covers', 'public');
            $validated['cover_img'] = '/storage/' . $path;
        }

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('games/logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        // Update the game record
        $game->update($validated);

        // --- NEW GENRE LOGIC & SYNC ---
        $syncIds = [];
        if ($request->has('genres')) {
            foreach ($request->input('genres') as $genreItem) {
                if (is_numeric($genreItem)) {
                    // Existing genre ID
                    $syncIds[] = (int) $genreItem;
                } else {
                    // New genre string - find it or create it
                    // Your Genre model already auto-generates the slug in the boot() method!
                    $newGenre = Genre::firstOrCreate([
                        'name' => trim($genreItem)
                    ]);
                    $syncIds[] = $newGenre->id;
                }
            }
        }
        
        $game->genres()->sync($syncIds);

        // --- GARBAGE COLLECTION ---
        // Delete any genres that are no longer attached to any games
        Genre::doesntHave('games')->delete();

        return redirect('/games/' . $game->id)->with('success', 'Game information updated successfully.');
    }
    
    public function apiIndex()
    {
        $games = Game::select('id', 'title', 'publisher', 'release_date', 'average_rating')
            ->orderBy('average_rating', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Pobrano listę najlepszych gier',
            'data' => $games
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}