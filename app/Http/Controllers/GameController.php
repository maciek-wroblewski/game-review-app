<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Gate;

class GameController extends Controller
{
    public function create()
    {
        $this->authorize('create', Game::class);

        $genres = Cache::remember('genres_list', 86400, fn() => Genre::orderBy('name')->get());

        return view('games.create', compact('genres'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Game::class);

        $validated = $this->validateGame($request);
        $validated = $this->handleUploads($request, $validated);

        $game = Game::create($validated);
        $this->syncRelations($game, $request);

        return redirect('/games/'.$game->id)->with('success', 'Game created successfully.');
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);

        $games = Cache::remember('games_index_page_'.$page, 600, function () {
            return Game::with(['genres', 'credits' => function ($query) {
                $query->withPivot('role');
            }])
                ->withCount('reviews')
                ->orderBy('average_rating', 'desc')
                ->paginate(12);
        });

        if ($request->ajax()) {
            $html = '';
            foreach ($games as $game) {
                $html .= Blade::render('<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                                            <x-game.card :game="$game" />
                                        </div>', ['game' => $game]);
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
        $game->loadCount('reviews');

        $userId = auth()->id() ?? null;
        $user = auth()->user();
        $playlists = $user
            ? $user->playlists()->with(['games' => function ($query) use ($game) {
                $query->where('games.id', $game->id);
            }])->get()
            : collect();

        $userReviewPost = $userId
                    ? $game->reviews()
                        ->where('user_id', $userId)
                        ->withFeedRelations()
                        ->first()
                    : null;

        $posts = $game->reviews()
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
        Log::info('Viewing game: '.$game->title.' (ID: '.$game->id.') by user ID: '.($userId ?? 'guest'));

        return view('games.show', compact('game', 'playlists', 'userReviewPost', 'posts'));
    }

    public function loadMore(Request $request)
    {
        $page = $request->get('page', 1);

        $games = Game::with(['genres', 'credits' => function ($query) {
            $query->withPivot('role');
        }])
            ->withCount('reviews') // <-- CHANGED from 'posts'
            ->orderBy('average_rating', 'desc')
            ->paginate(12, ['*'], 'page', $page);

        $html = '';
        foreach ($games as $game) {
            $html .= Blade::render('<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">
                                        <x-game.card :game="$game" />
                                    </div>',
                                    ['game' => $game]);
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
        $this->authorize('update', $game);

        $genres = Cache::remember('genres_list', 86400, fn() => Genre::orderBy('name')->get());

        return view('games.edit', compact('game', 'genres'));
    }

    public function update(Request $request, Game $game)
    {
        $this->authorize('update', $game);

        $validated = $this->validateGame($request);
        $validated = $this->handleUploads($request, $validated);

        $game->update($validated);
        $this->syncRelations($game, $request);

        return redirect('/games/'.$game->id)->with('success', 'Game information updated successfully.');
    }

    /* =========================================================================
     * PRIVATE HELPER METHODS (The magic happens here)
     * ========================================================================= */

    /**
     * Standardizes validation rules for creating and updating.
     */
    private function validateGame(Request $request): array
    {
        return $request->validate([
            'title'        => 'required|string|max:255',
            'publisher'    => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'details'      => 'nullable|string',
            'banner_img'   => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_img'    => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo'         => 'nullable|image|mimes:jpeg,png,webp|max:1024',
            'genres'       => 'nullable|array',
            'credits'      => 'nullable|array',
        ]);
    }

    /**
     * DRYs out the file upload process for banners, covers, and logos.
     */
    private function handleUploads(Request $request, array $validated): array
    {
        $files = [
            'banner_img' => 'games/banners',
            'cover_img'  => 'games/covers',
            'logo'       => 'games/logos',
        ];

        foreach ($files as $inputName => $directory) {
            if ($request->hasFile($inputName)) {
                $path = $request->file($inputName)->store($directory, 'public');
                $validated[$inputName] = '/storage/' . $path;
            }
        }

        return $validated;
    }

    /**
     * Handles the complex logic of syncing Genres and Credits.
     */
    private function syncRelations(Game $game, Request $request): void
    {
        // 1. Sync Genres
        if ($request->has('genres')) {
            $syncIds = [];
            foreach ($request->input('genres') as $genreItem) {
                $syncIds[] = is_numeric($genreItem) 
                    ? (int) $genreItem 
                    : Genre::firstOrCreate(['name' => trim($genreItem)])->id;
            }
            $game->genres()->sync($syncIds);
        }

        // 2. Sync Credits
        if ($request->has('credits') && is_array($request->input('credits'))) {
            $syncCredits = collect($request->input('credits'))->mapWithKeys(function ($value, $key) {
                if (is_array($value) && isset($value['role'])) {
                    return [$key => ['role' => $value['role']]];
                } elseif (is_numeric($value)) {
                    return [$value => ['role' => 'Developer']];
                }
                return []; // Ignore invalid structures
            })->toArray();

            $game->credits()->sync($syncCredits);
        }
    }

    // --- API METHODS ---

    public function apiIndex(Request $request)
    {
        $query = Game::select('id', 'title', 'publisher', 'release_date', 'average_rating', 'cover_img')
            ->with(['genres:id,name']); // Only grab the ID and name of the genre

        // Optional: Allow filtering by genre (e.g., /api/v1/games?genre=RPG)
        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('name', $request->query('genre'));
            });
        }

        // Use pagination instead of ->take(10) so clients can load more pages
        // Create a unique cache key based on the page number and genre filter
        $cacheKey = 'api_games_page_'.$request->get('page', 1).'_genre_'.$request->get('genre', 'all');

        // Cache the API results for 5 minutes (300 seconds)
        $games = Cache::remember($cacheKey, 300, function () use ($query) {
            return $query->orderBy('average_rating', 'desc')->paginate(15);
        });

        return response()->json([
            'success' => true,
            'message' => 'Fetched games successfully.',
            'data' => $games,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function apiShow(Game $game)
    {
        // Load relationships, restricting the fields to prevent leaking sensitive user data
        $game->load([
            'genres:id,name',
            'credits' => function ($query) {
                $query->select('users.id', 'username')->withPivot('role');
            },
        ]);

        return response()->json([
            'success' => true,
            'data' => $game,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function apiReviews(Game $game)
    {
        $reviews = $game->reviews()
            ->with([
                'author:id,username,avatar_media_id',
                'author.avatar',
                'review',
            ])
            ->latest()
            ->paginate(10);

        // Map over the results to format them nicely for the API consumer
        $formattedReviews = $reviews->getCollection()->map(function ($post) {
            return [
                'id' => $post->id,
                'author' => $post->author->username,
                'avatar_url' => $post->author->avatar_url ?? null,
                'rating' => $post->review->rating,
                'type' => $post->review->type,
                'body' => $post->body,
                'is_spoiler' => $post->is_spoiler,
                'likes_count' => $post->likes_count ?? 0,
                'created_at' => $post->created_at->toIso8601String(),
            ];
        });

        // Replace the unformatted collection with the clean one
        $reviews->setCollection($formattedReviews);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}
