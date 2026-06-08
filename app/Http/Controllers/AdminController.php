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
            ->with('author') // <--- Change 'user' to 'author'
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
        if (!auth()->check() || !auth()->user()->is_admin) abort(403);
        $user->update(['verified' => !$user->verified]);
        return back();
    }

    public function toggleAdmin(User $user)
    {
        if (!auth()->check() || !auth()->user()->is_admin) abort(403);
        if ($user->id === auth()->id()) return back(); // Prevent removing own admin
        
        $user->update(['is_admin' => !$user->is_admin]);
        return back();
    }

    public function toggleSuspend(User $user)
    {
        if (!auth()->check() || !auth()->user()->is_admin) abort(403);
        if ($user->id === auth()->id()) return back(); // Prevent suspending self
        
        $user->update(['is_suspended' => !$user->is_suspended]);
        return back();
    }

    public function togglePinned(Post $post)
    {
        if (!auth()->check() || !auth()->user()->is_admin) abort(403);
        $post->update(['is_pinned' => !$post->is_pinned]);
        return back();
    }

    public function toggleLock(Post $post)
    {
        if (!auth()->check() || !auth()->user()->is_admin) abort(403);
        
        $post->update(['admin_locked' => !$post->admin_locked]);
        
        return back();
    }
}