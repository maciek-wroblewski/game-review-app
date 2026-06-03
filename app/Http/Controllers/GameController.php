<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreGameRequest;
use App\Http\Requests\UpdateGameRequest;
use App\Models\Game;
use App\Models\Genre;
use App\Models\Post;
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

    public function store(StoreGameRequest $request)
    {
        $this->authorize('create', Game::class);

        $validated = $request->validated();
        $validated = $this->handleUploads($request, $validated);

        $game = Game::create($validated);
        $this->syncRelations($game, $request);

        return redirect('/games/'.$game->id)->with('success', __('common.game_created'));
    }

    public function index(Request $request)
    {
        $page = $request->get('page', 1);

        $games = Game::with(['genres', 'credits' => function ($query) {
            $query->withPivot('role');
        }])
            ->withCount(['posts as reviews_count' => function ($query) {
                $query->has('review');
            }])
            ->orderBy('average_rating', 'desc')
            ->paginate(12);

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

        // Fix: Use the relationship method to avoid morph map issues and cache the query.
        $game->reviews_count = Cache::remember("game_{$game->id}_reviews_count", 3600, function() use ($game) {
            return $game->reviews()->count();
        });

        $userId = auth()->id() ?? null;
        $user = auth()->user();
        
        // Cache user playlists query related to this game
        $playlists = $user
            ? Cache::remember("user_{$userId}_game_{$game->id}_playlists", 300, function() use ($user, $game) {
                return $user->playlists()->with(['games' => function ($query) use ($game) {
                    $query->where('games.id', $game->id);
                }])->get();
            })
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
            ->withCount(['posts as reviews_count' => function ($query) {
                $query->has('review');
            }])
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
            ? Cache::remember("user_{$userId}_game_{$game->id}_playlists", 300, function() use ($user, $game) {
                return $user->playlists()->with(['games' => function ($query) use ($game) {
                    $query->where('games.id', $game->id);
                }])->get();
            })
            : collect();

        $posts = $game->posts()
            ->doesntHave('review')
            ->withMinimalFeedRelations(['hub' => false, 'review' => false])
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(10);

        $posts->getCollection()->each(function ($post) use ($game) {
            $post->setRelation('hub', $game);
            $post->setRelation('review', null);

            if ($post->relationLoaded('parent') && $post->parent) {
                $post->parent->setRelation('hub', $game);
                $post->parent->setRelation('review', null);
            }
        });

        // FIXED: Changed $request->request->ajax() to $request->ajax()
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

    public function update(UpdateGameRequest $request, Game $game)
    {
        $this->authorize('update', $game);

        $validated = $request->validated();
        $validated = $this->handleUploads($request, $validated);

        $game->update($validated);
        $this->syncRelations($game, $request);

        return redirect('/games/'.$game->id)->with('success', __('common.game_updated'));
    }

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

    private function syncRelations(Game $game, Request $request): void
    {
        if ($request->has('genres')) {
            $syncIds = [];
            foreach ($request->input('genres') as $genreItem) {
                $syncIds[] = is_numeric($genreItem) 
                    ? (int) $genreItem 
                    : Genre::firstOrCreate(['name' => trim($genreItem)])->id;
            }
            $game->genres()->sync($syncIds);
        }

        if ($request->has('credits') && is_array($request->input('credits'))) {
            $syncCredits = collect($request->input('credits'))->mapWithKeys(function ($value, $key) {
                if (is_array($value) && isset($value['role'])) {
                    return [$key => ['role' => $value['role']]];
                } elseif (is_numeric($value)) {
                    return [$value => ['role' => 'Developer']];
                }
                return []; 
            })->toArray();

            $game->credits()->sync($syncCredits);
        }
    }

    public function apiIndex(Request $request)
    {
        $query = Game::select('id', 'title', 'publisher', 'release_date', 'average_rating', 'cover_img')
            ->with(['genres:id,name']); 

        if ($request->has('genre')) {
            $query->whereHas('genres', function ($q) use ($request) {
                $q->where('name', $request->query('genre'));
            });
        }

        $cacheKey = 'api_games_page_'.$request->get('page', 1).'_genre_'.$request->get('genre', 'all');

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

        $reviews->setCollection($formattedReviews);

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}