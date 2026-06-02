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
            // Abort with 403 Forbidden or redirect to a standard restricted template view
            abort(403, 'This profile is private.');
        }
    }

    public function show(Request $request, User $user)
    {
        $user->loadCount([
            'followers',
            'following',
            'reviews',
            'posts',
            'playlists',
        ])
            ->load([
                'settings',
                'avatar',
            ]);

        if (! $user->canViewProfile(Auth::user())) {
            return view('users.private', ['user' => $user]);
        }

        // 1. Authored Posts
        $posts = $user->posts()
            ->latest()
            ->withFeedRelations(['review' => false, 'author' => false]) 
            ->paginate(10, ['*'], 'posts_page');

        // Manually attach the author AND tell it there is no review
        $posts->getCollection()->each(function ($post) use ($user) {
            $post->setRelation('author', $user);
            $post->setRelation('review', null); // Fixes the N+1
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

        // Manually attach the hub AND tell it there is no review
        $comments->getCollection()->each(function ($comment) use ($user) {
            $comment->setRelation('hub', $user);
            $comment->setRelation('review', null); // Fixes the N+1
        });

        // 3. Handle AJAX Requests for loading more posts/comments dynamically
        if ($request->ajax()) {
            if ($request->has('posts_page')) {
                return response()->json([
                    'html' => view('components.post.items', compact('posts'))->render(),
                    'next_page_url' => $posts->nextPageUrl(),
                ]);
            }

            if ($request->has('comments_page')) {
                return response()->json([
                    'html' => view('components.post.items', ['posts' => $comments])->render(),
                    'next_page_url' => $comments->nextPageUrl(),
                ]);
            }
        }
        Log::info('Viewing profile: '.$user->username.' (ID: '.$user->id.') by '.(Auth::check() ? Auth::user()->username : 'guest'));
        return view('users.show', compact('user', 'posts', 'comments'));
    }

    public function followers(Request $request, User $user)
    {
        $followers = $user->followers()->latest()->paginate(20);

        // Intercept async pagination queries
        if ($request->ajax()) {
            $html = '';
            foreach ($followers as $follower) {
                // Fix: Map '$follower' variable explicitly to 'user' expected by the compact partial
                $html .= view('users.partials.compact-card-wrapper', ['user' => $follower])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page_url' => $followers->nextPageUrl(),
            ]);
        }

        return view('users.followers', compact('user', 'followers'));
    }

    public function following(Request $request, User $user)
    {
        $following = $user->following()->latest()->paginate(20);

        // Intercept async pagination queries
        if ($request->ajax()) {
            $html = '';
            foreach ($following as $followedUser) {
                // Fix: Map '$followedUser' variable explicitly to 'user' expected by the compact partial
                $html .= view('users.partials.compact-card-wrapper', ['user' => $followedUser])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page_url' => $following->nextPageUrl(),
            ]);
        }

        return view('users.following', compact('user', 'following'));
    }

    public function playlists(Request $request, User $user)
    {
        $playlists = $user->playlists()
        ->with('users')       // Eager load users to prevent N+1 on ownership check
        ->withCount('games')  // Perform the count in SQL natively
        ->latest()
        ->paginate(20);

        // Intercept async pagination queries
        if ($request->ajax()) {
            $html = '';
            foreach ($playlists as $playlist) {
                // Loop and render the exact partial template required for playlists
                $html .= view('components.playlist.card', ['playlist' => $playlist, 'layout' => 'compact'])->render();
            }

            return response()->json([
                'html' => $html,
                'next_page_url' => $playlists->nextPageUrl(),
            ]);
        }

        return view('users.playlists', compact('user', 'playlists'));
    }

    public function reviews(Request $request, User $user)
    {
        $posts = $user->reviews()
            ->latest()
            ->withFeedRelations()
            ->orderByDesc('is_pinned')
            ->latest()
            ->paginate(5);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(),
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        return view('users.reviews', compact('user', 'posts'));
    }

    public function posts(Request $request, User $user)
    {
        $posts = $user->posts()
            ->latest()
            ->withFeedRelations()
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(),
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        return view('users.posts', compact('user', 'posts'));
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

        $results = $users->take(10)->get()->map(function($u) {
            return [
                'id' => $u->id,
                'username' => $u->username,
                'avatar_url' => $u->avatar_url
            ];
        });

        return response()->json($results);
    }
}
