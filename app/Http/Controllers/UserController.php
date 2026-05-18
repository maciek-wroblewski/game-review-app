<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Auth;

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

    public function followers(User $user)
    {
        $this->checkProfileAccess($user); // <-- Security check added

        $followers = $user->followers()->latest()->paginate(20);
        return view('users.followers', compact('user', 'followers'));
    }

    public function following(User $user)
    {
        $this->checkProfileAccess($user); // <-- Security check added

        $following = $user->following()->latest()->paginate(20);
        return view('users.following', compact('user', 'following'));
    }

    public function playlists(User $user)
    {
        $this->checkProfileAccess($user); // <-- Security check added

        $playlists = $user->playlists()->latest()->paginate(20);
        return view('users.playlists', compact('user', 'playlists'));
    }

    public function reviews(User $user)
    {
        $this->checkProfileAccess($user); // <-- Security check added

        $reviews = $user->posts()
            ->has('review')
            ->latest()
            ->paginate(15);

        return view('users.reviews', compact('user', 'reviews'));
    }
}