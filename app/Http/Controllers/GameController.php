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
use App\Http\Controllers\Concerns\HasPaginatedResponses;

class GameController extends Controller
{
    use HasPaginatedResponses;
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
        $games = Game::with(['genres', 'credits:id,username'])
            ->withCount('reviews')
            ->orderBy('average_rating', 'desc')
            ->simplePaginate(12);

        if ($request->ajax()) {
            return $this->ajaxCardGrid($games, 'components.game.card', 'game');
        }

        $gamesTotal = Cache::remember('games_total_count', 3600, fn() => Game::count());

        return view('games.index', compact('games', 'gamesTotal'));
    }

    public function show(Request $request, Game $game)
    {
        if ($game->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('games.this_game_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('games.deleted');
        }

        $game = Cache::remember("game_show_model_{$game->id}", 3600, function() use ($game) {
            $game->load(['genres', 'credits']);
            return $game;
        });

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
                        ->withFeedRelations(['hub' => false])
                        ->first()
                    : null;
        if ($userReviewPost) {
            $userReviewPost->setRelation('hub', $game);
        }

        $posts = Cache::remember("game_{$game->id}_reviews_page_" . $request->get('page', 1), 600, function() use ($game) {
            return $game->reviews()
                ->withFeedRelations(['hub' => false])
                ->orderByDesc('is_pinned')
                ->latest()
                ->simplePaginate(10);
        });

        $posts->getCollection()->each(function ($post) use ($game) {
            $post->setRelation('hub', $game);
            if ($post->relationLoaded('parent') && $post->parent) {
                $post->parent->setRelation('hub', $game);
            }
        });

        if ($userId) {
            $posts->setCollection(
                $posts->getCollection()->filter(fn($post) => $post->user_id !== $userId)
            );
        }

        $this->setLikedByAuthForPosts($posts);
        if ($userReviewPost) {
            $this->setLikedByAuthForPosts($userReviewPost);
        }

        if ($request->ajax()) {
            return view('components.post.items', compact('posts'))->render();
        }
        Log::info('Viewing game: '.$game->title.' (ID: '.$game->id.') by user ID: '.($userId ?? 'guest'));

        return view('games.show', compact('game', 'playlists', 'userReviewPost', 'posts'));
    }


    public function discussions(Request $request, Game $game)
    {
        if ($game->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('games.this_game_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('games.deleted');
        }

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
            ->simplePaginate(10);

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

        Cache::forget("game_show_model_{$game->id}");

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

        Log::info('API: Fetched games list, page '.$request->get('page', 1).', genre: '.$request->get('genre', 'all'));
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
        Log::info('API: Fetched game details for game ID: '.$game->id);
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
        Log::info('API: Fetched reviews for game ID: '.$game->id.' - Total reviews: '.$reviews->total());

        return response()->json([
            'success' => true,
            'data' => $reviews,
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function destroy(Game $game)
    {
        $this->authorize('delete', $game);

        $gameId = $game->id;
        $game->delete();

        // Clear cache
        Cache::forget("game_show_model_{$gameId}");
        Cache::forget('games_total_count');

        return redirect('/games')->with('success', __('common.game_deleted'));
    }
}