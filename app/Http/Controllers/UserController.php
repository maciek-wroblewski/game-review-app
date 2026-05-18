<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function show(User $user)
    {
        // Leverage explicit model binding & load counts smoothly
        $user->loadCount(['followers', 'following', 'posts', 'playlists'])
             ->load('settings');

        if (!$user->canViewProfile(Auth::user())) {
            return view('users.private', ['user' => $user]);
        }

        // Clean, readable, and highly optimized via Post model query scopes
        $recentPosts = $user->posts()
            ->latest()
            ->withFeedRelations()
            ->paginate(10);

        $user->setRelation('posts', $recentPosts);

        return view('users.show', ['user' => $user]);
    }

    public function followers(User $user)
    {
        $followers = $user->followers()->latest()->paginate(20);
        return view('users.followers', compact('user', 'followers'));
    }

    public function following(User $user)
    {
        $following = $user->following()->latest()->paginate(20);
        return view('users.following', compact('user', 'following'));
    }

    public function playlists(User $user)
    {
        $playlists = $user->playlists()->latest()->paginate(20);
        return view('users.playlists', compact('user', 'playlists'));
    }

    public function reviews(User $user)
    {
        // OPTIMIZATION: Ensure we strictly pull items that have an associated review record
        $reviews = $user->posts()
            ->has('review')
            ->with(['review.game'])
            ->latest()
            ->paginate(15);

        return view('users.reviews', compact('user', 'reviews'));
    }
}