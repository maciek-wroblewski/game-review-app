<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Genre;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{

    public function create()
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, __('You do not have permission to create a game.'));
        }

        $genres = Genre::orderBy('name')->get(); 
        return view('games.create', compact('genres')); 
    }

    public function store(Request $request)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, __('You do not have permission to create a game.'));
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'release_date' => 'nullable|date',
            'details' => 'nullable|string',
            'banner_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'cover_img' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'logo' => 'nullable|image|mimes:jpeg,png,webp|max:1024',
            'genres' => 'nullable|array',
            'credits' => 'nullable|array', // Validate credits array
        ]);

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

        $game = Game::create($validated);

        // Sync Genres
        $syncIds = [];
        if ($request->has('genres')) {
            foreach ($request->input('genres') as $genreItem) {
                if (is_numeric($genreItem)) {
                    $syncIds[] = (int) $genreItem;
                } else {
                    $newGenre = Genre::firstOrCreate(['name' => trim($genreItem)]);
                    $syncIds[] = $newGenre->id;
                }
            }
        }
        $game->genres()->sync($syncIds);

        // --- UPDATED SYNC CREDITS LOGIC ---
        // --- NEW CREDITS LOGIC & SYNC ---
        $syncCredits = [];
        
        if ($request->has('credits') && is_array($request->input('credits'))) {
            foreach ($request->input('credits') as $key => $value) {
                // Scenario 1: The input has roles (e.g., credits[1][role] = 'Developer')
                // Here, $key is the User ID, and $value is the array ['role' => 'Developer']
                if (is_array($value) && isset($value['role'])) {
                    $syncCredits[$key] = ['role' => $value['role']];
                } 
                // Scenario 2: Fallback for inputs without roles (e.g., playlists sending plain IDs)
                // Here, $value is the User ID itself
                elseif (is_numeric($value)) {
                    $syncCredits[$value] = ['role' => 'Developer']; // Default role
                }
            }
        }
        
        $game->credits()->sync($syncCredits);

        return redirect('/games/' . $game->id)->with('success', 'Game created successfully.');
    }

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
        Log::info('Viewing game: '.$game->title.' (ID: '.$game->id.') by user ID: '.($userId ?? 'guest'));

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

        // --- NEW CREDITS LOGIC & SYNC ---
        $syncCredits = [];
        
        if ($request->has('credits') && is_array($request->input('credits'))) {
            foreach ($request->input('credits') as $key => $value) {
                // Scenario 1: The input has roles (e.g., credits[1][role] = 'Developer')
                // Here, $key is the User ID, and $value is the array ['role' => 'Developer']
                if (is_array($value) && isset($value['role'])) {
                    $syncCredits[$key] = ['role' => $value['role']];
                } 
                // Scenario 2: Fallback for inputs without roles (e.g., playlists sending plain IDs)
                // Here, $value is the User ID itself
                elseif (is_numeric($value)) {
                    $syncCredits[$value] = ['role' => 'Developer']; // Default role
                }
            }
        }
        
        $game->credits()->sync($syncCredits);

        return redirect('/games/' . $game->id)->with('success', 'Game information updated successfully.');    
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
        $games = $query->orderBy('average_rating', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'message' => 'Fetched games successfully.',
            'data' => $games
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function apiShow(Game $game)
    {
        // Load relationships, restricting the fields to prevent leaking sensitive user data
        $game->load([
            'genres:id,name', 
            'credits' => function ($query) {
                $query->select('users.id', 'username')->withPivot('role');
            }
        ]);

        return response()->json([
            'success' => true,
            'data' => $game
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }

    public function apiReviews(Game $game)
    {
        $reviews = $game->posts()
            ->has('review')
            ->with([
                'author:id,username,avatar_media_id', // Only get public author info
                'author.avatar', // Assuming you have an avatar relation
                'review'         // Get the actual review data (rating, type)
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
            'data' => $reviews
        ], 200, [], JSON_UNESCAPED_UNICODE);
    }
}