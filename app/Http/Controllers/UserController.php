<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

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

    public function show(User $user)
    {
        $user->loadCount([
                'followers',
                'following',
                'posts',
                'playlists'
            ])
            ->load([
                'settings',
                'avatar'
            ]);

        if (! $user->canViewProfile(Auth::user())) {

            return view('users.private', [
                'user' => $user
            ]);
        }

        $recentPosts = $user->posts()
            ->latest()
            ->withFeedRelations()
            ->paginate(10);

        $user->setRelation('posts', $recentPosts);

        $posts = Post::query()
            ->where('hub_type', 'user')
            ->where('hub_id', $user->id)
            ->whereNull('parent_id')
            ->latest()
            ->withFeedRelations()
            ->paginate(10);

        return view('users.show', compact('user', 'posts'));
    }

    public function followers(Request $request, User $user)
    {
        $followers = $user->followers()
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {

            $html = '';

            foreach ($followers as $follower) {

                $html .= view(
                    'users.partials.follower-card-wrapper',
                    compact('follower')
                )->render();
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
        $following = $user->following()
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {

            $html = '';

            foreach ($following as $followedUser) {

                $html .= view(
                    'users.partials.following-card-wrapper',
                    compact('followedUser')
                )->render();
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
            ->latest()
            ->paginate(20);

        if ($request->ajax()) {

            $html = '';

            foreach ($playlists as $playlist) {

                $html .= view(
                    'users.partials.playlist-card-wrapper',
                    compact('playlist')
                )->render();
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
        $posts = $user->posts()
            ->whereNull('parent_id')
            ->latest()
            ->withFeedRelations()
            ->paginate(5);

        if ($request->ajax()) {

            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(),
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        return view('users.reviews', compact('user', 'posts'));
    }
}