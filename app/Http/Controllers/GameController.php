<?php

namespace App\Http\Controllers;

use App\Models\Game;
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

        // If it's an AJAX pagination request, return raw HTML row grids directly
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

    /**
     * Display the specified game.
     */
    public function show(Request $request, Game $game)
    {
        // 1. Eager load simple relations and an efficient COUNT of posts/reviews
        $game->load(['genres', 'credits']);
        $game->loadCount(['posts' => function ($query) {
            $query->whereHas('review');
        }]);

        $userId = auth()->id() ?? null;
        $user = auth()->user();
        $playlists = $user
            ? $user->playlists()->with(['games' => function ($query) use ($game) {
                $query->where('games.id', $game->id); // Only loads pivot data for this specific game
            }])->get()
            : collect();

        // 2. Fetch user's specific review first using the optimized feed scope
        $userReviewPost = $userId
                    ? $game->posts() // <-- Query directly through the relationship
                        ->where('user_id', $userId)
                        ->has('review')
                        ->withFeedRelations()
                        ->first()
                    : null;

        // 3. Fetch all other reviews using the optimized feed scope
        $posts = $game->posts() // <-- Query directly through the relationship
            ->has('review')
            ->withFeedRelations()
            ->when($userId, function ($query) use ($userId) {
                // This excludes the user's post from the paginated list
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

        // CHANGE: Render the partial that includes the column wrapper
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
        // 1. Eager load simple relations and an efficient COUNT of discussion posts
        $game->load(['genres', 'credits']);
        $game->loadCount(['posts' => function ($query) {
            $query->doesntHave('review'); // Filter out reviews for the count
        }]);

        $userId = auth()->id() ?? null;
        $user = auth()->user();
        
        $playlists = $user
            ? $user->playlists()->with(['games' => function ($query) use ($game) {
                $query->where('games.id', $game->id);
            }])->get()
            : collect();

        // 2. Fetch all discussion posts (excluding reviews) using the optimized feed scope
        $posts = $game->posts()
            ->doesntHave('review') // Get general discussions, not reviews
            ->withFeedRelations()
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        // 3. Handle AJAX pagination
        if ($request->ajax()) {
            return view('components.post.items', compact('posts'))->render();
        }

        return view('games.discussions', compact('game', 'playlists', 'posts'));
    }

    /**
     * Show the form for editing the specified game.
     */
    public function edit(Game $game)
    {
        // Authorization check: User must be authenticated AND either an admin or in the game's credits
        if (!auth()->check() || (!auth()->user()->is_admin && !$game->credits->contains('id', auth()->id()))) {
            abort(403, 'You do not have permission to edit this game.');
        }

        return view('games.edit', compact('game'));
    }

    /**
     * Update the specified game in storage.
     */
    public function update(Request $request, Game $game)
    {
        // Authorization check...
        if (!auth()->check() || (!auth()->user()->is_admin && !$game->credits->contains('id', auth()->id()))) {
            abort(403, 'You do not have permission to edit this game.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'details' => 'nullable|string',
            'banner_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,webp|max:1024', // <-- Changed to 'logo'
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

        // <-- Changed to 'logo' here as well
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('games/logos', 'public');
            $validated['logo'] = '/storage/' . $path;
        }

        // Update the game record
        $game->update($validated);

        return redirect('/games/' . $game->id)->with('success', 'Game information updated successfully.');
    }
}