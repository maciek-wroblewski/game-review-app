<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show($username)
    {
        $user = User::where('username', $username)
            ->with('settings')
            ->withCount(['followers', 'following', 'posts', 'playlists'])
            ->firstOrFail();

        if (!$user->canViewProfile(Auth::user())) {

            return view('users.private', [
                'user' => $user
            ]);
        }

        // Preload the user's recent posts with the relations used by the post component
        $recentPosts = $user->posts()
            ->latest()
            ->with([
                'author',
                'media',
                'review',
                'hub',
                'parent' => function ($query) {
                    $query->withCount('replies')
                          ->with(['author', 'media', 'review', 'hub']);
                },
            ])
            ->withCount('replies')
            ->get();

        if (Auth::check()) {
            Auth::user()->loadMissing('following');

            $likedPostIds = Auth::user()
                ->likedPosts()
                ->pluck('posts.id')
                ->all();

            $recentPosts->each(function ($post) use ($likedPostIds) {
                $post->setAttribute('liked_by_auth', in_array($post->id, $likedPostIds, true));
                if ($post->relationLoaded('parent') && $post->parent) {
                    $post->parent->setAttribute('liked_by_auth', in_array($post->parent->id, $likedPostIds, true));
                }
            });
        }

        // Attach the loaded collection to the user so the view can use $user->posts without extra queries
        $user->setRelation('posts', $recentPosts);

        return view('users.show', [
            'user' => $user
        ]);
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->latest()->get();

        return view('users.followers', [
            'user' => $user,
            'followers' => $followers,
        ]);
    }

    public function following(User $user)
    {
        $following = $user->following()->latest()->get();

        return view('users.following', [
            'user' => $user,
            'following' => $following,
        ]);

    }

    public function playlists(User $user)
    {
        $playlists = $user->playlists()->latest()->get();

        return view('users.playlists', [
            'user' => $user,
            'playlists' => $playlists, 
        ]);
    }

    public function reviews(User $user)
    {
        $reviews = $user->posts()->with(['review.game'])->latest()->get();

        return view('users.reviews', [
            'user' => $user,
            'reviews' => $reviews,
        ]);
    }
}
