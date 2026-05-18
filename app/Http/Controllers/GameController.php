<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Game;

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
        $user = \App\Models\User::find($userId, 'id');
        $playlists = $user ? $user->playlists : collect();

        // 2. Fetch user's specific review first using the optimized feed scope
        $userReviewPost = $userId 
            ? \App\Models\Post::whereMorphRelation('hub', Game::class, 'id', $game->id)
                ->where('user_id', $userId)
                ->has('review')
                ->withFeedRelations() // <-- Added optimized feed relations
                ->first()
            : null;

        // 3. Fetch all other reviews using the optimized feed scope
        $posts = \App\Models\Post::whereMorphRelation('hub', Game::class, 'id', $game->id)
            ->has('review')
            ->withFeedRelations() // <-- Added optimized feed relations
            ->when($userId, function ($query) use ($userId) {
                // This excludes the user's post from the paginated list
                $query->where('user_id', '!=', $userId);
            })
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
}
