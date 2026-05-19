<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Notification;

class AdminController extends Controller
{
    public function index()
    {
        if (!auth()->check() || !auth()->user()->is_admin) {

            abort(403);

        }

        $userCount = User::count();

        $postCount = Post::count();

        $reviewCount = Post::whereHas('review')->count();

        $notificationCount = Notification::count();

        $latestUsers = User::latest()
            ->take(5)
            ->get();

        $latestPosts = Post::latest()
            ->with('user')
            ->take(5)
            ->get();

        return view('admin.index', [

            'userCount' => $userCount,
            'postCount' => $postCount,
            'reviewCount' => $reviewCount,
            'notificationCount' => $notificationCount,

            'latestUsers' => $latestUsers,
            'latestPosts' => $latestPosts,

        ]);
    }

    public function verifyUser(User $user)
    {
        if (!auth()->check() || !auth()->user()->is_admin) {

            abort(403);

        }

        $user->update([

            'verified' => !$user->verified

        ]);

        return back();
    }
}