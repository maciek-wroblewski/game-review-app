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
            return view('users.private', ['user' => $user]);
        }

        // Delegate the relationship check to SQL using withExists subqueries
        $recentPosts = $user->posts()
            ->latest()
            ->with([
                'author',
                'media',
                'review',
                'hub',
                'parent' => function ($query) {
                    $query->withCount('replies')
                        ->with(['author', 'media', 'review', 'hub'])
                        ->when(auth()->check(), function ($q) {
                            $q->withExists(['likes as liked_by_auth' => function ($sq) {
                                $sq->where('user_id', auth()->id());
                            }]);
                        });
                },
            ])
            ->withCount('replies')
            ->when(auth()->check(), function ($query) {
                $query->withExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            })
            ->paginate(10); // <-- Paginate to keep memory footprint safe and fixed

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
        $reviews = $user->posts()->with(['review.game'])->latest()->paginate(15);
        return view('users.reviews', compact('user', 'reviews'));
    }
}
