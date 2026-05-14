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
        $user = User::where('username', $username)->firstOrFail();

        if (!$user->canViewProfile(Auth::user())) {

            return view('users.private', [
                'user' => $user
            ]);
        }

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
        $reviews = $user->posts()->latest()->get();

        return view('users.reviews', [
            'user' => $user,
            'reviews' => $reviews,
        ]);
    }
}
