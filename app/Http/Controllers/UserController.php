<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Common guard to check profile permissions across all sub-pages.
     */
    protected function checkProfileAccess(User $user)
    {
        $user->loadMissing('settings');

        if (!$user->canViewProfile(Auth::user())) {
            // Abort with 403 Forbidden or redirect to a standard restricted template view
            abort(403, 'This profile is private.');
        }
    }

    public function show(User $user)
    {
        $user->loadCount(['followers', 'following', 'posts', 'playlists'])
            ->load('settings');

        if (!$user->canViewProfile(Auth::user())) {
            return view('users.private', ['user' => $user]);
        }

        $recentPosts = $user->posts()
            ->latest()
            ->withFeedRelations()
            ->paginate(10);

        $user->setRelation('posts', $recentPosts);

        return view('users.show', ['user' => $user]);
    }

    public function followers(Request $request, User $user)
    {
        $followers = $user->followers()->latest()->paginate(20);

        // Intercept async pagination queries
        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(), // for reviews
                'next_page_url' => $posts->nextPageUrl()
            ]);
        }

        return view('users.followers', compact('user', 'followers'));
    }

    public function following(Request $request, User $user)
    {
        $following = $user->following()->latest()->paginate(20);

        // Intercept async pagination queries
        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(), // for reviews
                'next_page_url' => $posts->nextPageUrl()
            ]);
        }

        return view('users.following', compact('user', 'following'));
    }

    public function playlists(Request $request, User $user)
    {
        $playlists = $user->playlists()->latest()->paginate(20);

        // Intercept async pagination queries
        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(), // for reviews
                'next_page_url' => $posts->nextPageUrl()
            ]);
        }
        return view('users.playlists', compact('user', 'playlists'));
    }

    public function reviews(Request $request, User $user)
    {
        // 1. Query only posts containing review records, using your unified feed relationship scope
        $posts = $user->posts()
            ->has('review')
            ->withFeedRelations() // Loads author, media, review, game hub, parent thread info, likes, etc.
            ->latest()
            ->paginate(5); // Works perfectly with the 'page=X' logic in your component's JS

        // 2. Intercept AJAX pagination requests from the <x-post.list> JS script
        if ($request->ajax()) {
            return response()->json([
                'html' => view('components.post.items', compact('posts'))->render(), // for reviews
                'next_page_url' => $posts->nextPageUrl()
            ]);
        }

        // 3. Normal page loading falls back here
        return view('users.reviews', compact('user', 'posts'));
    }
}
