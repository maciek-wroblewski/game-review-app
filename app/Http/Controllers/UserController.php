<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Concerns\HasPaginatedResponses;

class UserController extends Controller
{
    use HasPaginatedResponses;
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
        if ($user->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_user_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('users.deleted');
        }

        // Preload all counts in a single batch query and cache it
        $user = \Illuminate\Support\Facades\Cache::remember("user_profile_model_{$user->id}", 3600, function() use ($user) {
            $user->loadCount(['followers', 'following', 'playlists', 'posts', 'reviews'])
                 ->load(['settings', 'avatar']);
            return $user;
        });

        if (! $user->canViewProfile(Auth::user())) {
            return view('users.private', compact('user'));
        }

        // 1. Authored Posts
        $postsPage = $request->get('posts_page', 1);
        $posts = \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_authored_posts_page_{$postsPage}", 3600, function() use ($user) {
            return Post::where('user_id', $user->id)
                ->latest()
                ->withFeedRelations(['author' => false])
                ->simplePaginate(3, ['*'], 'posts_page');
        });

        $posts->getCollection()->each(fn($post) => $post->setRelation('author', $user));

        // 2. Profile Comments
        $commentsPage = $request->get('comments_page', 1);
        $comments = \Illuminate\Support\Facades\Cache::remember("user_{$user->id}_profile_comments_page_{$commentsPage}", 3600, function() use ($user) {
            return Post::query()
                ->where('hub_type', 'user')
                ->where('hub_id', $user->id)
                ->whereNull('parent_id')
                ->orderByDesc('is_pinned')
                ->latest()
                ->withFeedRelations(['hub' => false, 'review' => false]) 
                ->simplePaginate(3, ['*'], 'comments_page');
        });

        $comments->getCollection()->each(function ($comment) use ($user) {
            $comment->setRelation('hub', $user);
            $comment->setRelation('review', null); 
        });

        $this->setLikedByAuthForPosts($posts);
        $this->setLikedByAuthForPosts($comments);

        // 3. Handle AJAX Requests
        if ($request->ajax()) {
            if ($request->has('posts_page')) {
                return $this->ajaxFeed($posts);
            }
            if ($request->has('comments_page')) {
                return $this->ajaxFeed($comments);
            }
        }

        Log::info('Viewing profile: '.$user->username.' (ID: '.$user->id.') by '.(Auth::check() ? Auth::user()->username : 'guest'));
        
        return view('users.show', compact('user', 'posts', 'comments'));
    }

    public function followers(Request $request, User $user)
    {
        if ($user->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_user_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('users.deleted');
        }

        $connections = $user->followers()
            ->withCompactCounts()
            ->latest()
            ->simplePaginate(20);

        if ($request->ajax()) {
            return $this->ajaxCardGrid($connections, 'components.user.card', 'user', ['layout' => 'compact']);
        }

        return view('users.connections', ['user' => $user, 'connections' => $connections, 'type' => 'followers']);
    }

    public function following(Request $request, User $user)
    {
        if ($user->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_user_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('users.deleted');
        }

        $connections = $user->following()
            ->withCompactCounts()
            ->latest()
            ->simplePaginate(20);

        if ($request->ajax()) {
            return $this->ajaxCardGrid($connections, 'components.user.card', 'user', ['layout' => 'compact']);
        }

        return view('users.connections', ['user' => $user, 'connections' => $connections, 'type' => 'following']);
    }

    public function playlists(Request $request, User $user)
    {
        if ($user->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_user_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('users.deleted');
        }

        $playlists = $user->playlists()
            ->with('users')
            ->withCount('games')
            ->latest()
            ->simplePaginate(6);

        if ($request->ajax()) {
            return $this->ajaxCardGrid($playlists, 'components.playlist.card', 'playlist', ['layout' => 'compact'], false);
        }

        return view('users.playlists', compact('user', 'playlists'));
    }

    public function reviews(Request $request, User $user)
    {
        if ($user->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_user_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('users.deleted');
        }

        $posts = $user->reviews()
            ->withFeedRelations(['author' => false])
            ->orderByDesc('is_pinned')
            ->latest()
            ->simplePaginate(5);

        // Set the author relation to the already-loaded profile user to avoid N+1
        $posts->getCollection()->each(fn($post) => $post->setRelation('author', $user));

        if ($request->ajax()) {
            return $this->ajaxFeed($posts);
        }

        return view('users.feed', ['user' => $user, 'posts' => $posts, 'type' => 'reviews']);
    }

    public function posts(Request $request, User $user)
    {
        if ($user->trashed()) {
            if ($request->ajax()) {
                return response()->json([
                    'message' => __('common.this_user_has_been_deleted'),
                    'success' => false
                ], 404);
            }
            return view('users.deleted');
        }

        $posts = $user->posts()
            ->latest()
            ->withFeedRelations(['author' => false, 'review' => false])
            ->simplePaginate(10);

        // Set the author relation to the already-loaded profile user to avoid N+1
        $posts->getCollection()->each(function ($post) use ($user) {
            $post->setRelation('author', $user);
            $post->setRelation('review', null);

            if ($post->relationLoaded('parent') && $post->parent) {
                $post->parent->setRelation('review', null);
            }
        });

        if ($request->ajax()) {
            return $this->ajaxFeed($posts);
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

}