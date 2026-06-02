<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    /**
     * Common guard to check profile permissions across all sub-pages.
     */
    protected function checkProfileAccess(User $user)
    {
        $user->loadMissing('settings');

        if (! $user->canViewProfile(Auth::user())) {
            abort(403, 'This profile is private.');
        }
    }

    public function show(Request $request, User $user)
    {
        $user->loadCount(['followers', 'following', 'reviews', 'posts', 'playlists'])
             ->load(['settings', 'avatar']);

        if (! $user->canViewProfile(Auth::user())) {
            return view('users.private', compact('user'));
        }

        // 1. Authored Posts
        $posts = $user->posts()
            ->latest()
            ->withFeedRelations(['review' => false, 'author' => false]) 
            ->paginate(10, ['*'], 'posts_page');

        $posts->getCollection()->each(function ($post) use ($user) {
            $post->setRelation('author', $user);
            $post->setRelation('review', null); 
        });

        // 2. Profile Comments
        $comments = Post::query()
            ->where('hub_type', 'user')
            ->where('hub_id', $user->id)
            ->whereNull('parent_id')
            ->orderByDesc('is_pinned')
            ->latest()
            ->withFeedRelations(['hub' => false, 'review' => false]) 
            ->paginate(10, ['*'], 'comments_page');

        $comments->getCollection()->each(function ($comment) use ($user) {
            $comment->setRelation('hub', $user);
            $comment->setRelation('review', null); 
        });

        // 3. Handle AJAX Requests
        if ($request->ajax()) {
            if ($request->has('posts_page')) {
                return $this->respondWithAjaxFeed($posts);
            }
            if ($request->has('comments_page')) {
                return $this->respondWithAjaxFeed($comments);
            }
        }

        Log::info('Viewing profile: '.$user->username.' (ID: '.$user->id.') by '.(Auth::check() ? Auth::user()->username : 'guest'));
        
        return view('users.show', compact('user', 'posts', 'comments'));
    }

    public function followers(Request $request, User $user)
    {
        $connections = $user->followers()->latest()->paginate(20);

        if ($request->ajax()) {
            return $this->respondWithAjaxCards($connections, 'components.user.card', 'user', ['layout' => 'compact']);
        }

        return view('users.connections', ['user' => $user, 'connections' => $connections, 'type' => 'followers']);
    }

    public function following(Request $request, User $user)
    {
        $connections = $user->following()->latest()->paginate(20);

        if ($request->ajax()) {
            return $this->respondWithAjaxCards($connections, 'components.user.card', 'user', ['layout' => 'compact']);
        }

        return view('users.connections', ['user' => $user, 'connections' => $connections, 'type' => 'following']);
    }

    public function playlists(Request $request, User $user)
    {
        $playlists = $user->playlists()
            ->with('users')
            ->withCount('games')
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {
            // Note: wrapInGrid is false here because your original playlist view didn't wrap them in col-divs inside the loop
            return $this->respondWithAjaxCards($playlists, 'components.playlist.card', 'playlist', ['layout' => 'compact'], false);
        }

        return view('users.playlists', compact('user', 'playlists'));
    }

    public function reviews(Request $request, User $user)
    {
        $posts = $user->reviews()
            ->withFeedRelations()
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(5);

        if ($request->ajax()) {
            return $this->respondWithAjaxFeed($posts);
        }

        return view('users.feed', ['user' => $user, 'posts' => $posts, 'type' => 'reviews']);
    }

    public function posts(Request $request, User $user)
    {
        $posts = $user->posts()
            ->latest()
            ->withFeedRelations()
            ->paginate(10);

        if ($request->ajax()) {
            return $this->respondWithAjaxFeed($posts);
        }

        return view('users.feed', ['user' => $user, 'posts' => $posts, 'type' => 'posts']);
    }

    public function searchApi(Request $request)
    {
        $query = $request->input('q', '');
        $filter = $request->input('filter', 'all');
        $authUser = auth()->user();

        $users = User::where('username', 'like', "%{$query}%");

        if ($authUser) {
            if ($filter === 'followers') {
                $users->whereIn('id', $authUser->followers()->select('users.id'));
            } elseif ($filter === 'following') {
                $users->whereIn('id', $authUser->following()->select('users.id'));
            } elseif ($filter === 'mutuals') {
                $users->whereIn('id', $authUser->mutuals()->select('users.id'));
            }
        }

        $results = $users->take(10)->get()->map(fn($u) => [
            'id' => $u->id,
            'username' => $u->username,
            'avatar_url' => $u->avatar_url
        ]);

        return response()->json($results);
    }

    /* =========================================================================
     * PRIVATE AJAX HELPER METHODS
     * ========================================================================= */

    /**
     * Handles standard feed pagination (posts, comments, reviews).
     */
    private function respondWithAjaxFeed($paginator)
    {
        return response()->json([
            'html' => view('components.post.items', ['posts' => $paginator])->render(),
            'next_page_url' => $paginator->nextPageUrl(),
        ]);
    }

    /**
     * Handles card grid pagination (users, playlists).
     */
    private function respondWithAjaxCards($paginator, string $viewName, string $dataKey, array $extraData = [], bool $wrapInGrid = true)
    {
        $html = '';
        
        foreach ($paginator as $item) {
            // Render the component's view directly instead of using the slower Blade::render() parser
            $content = view($viewName, array_merge([$dataKey => $item], $extraData))->render();
            
            if ($wrapInGrid) {
                $html .= '<div class="col-12 col-sm-6 col-lg-4 col-xl-3 animate-fade-in">' . $content . '</div>';
            } else {
                $html .= $content;
            }
        }

        return response()->json([
            'html' => $html,
            'next_page_url' => $paginator->nextPageUrl(),
        ]);
    }
}