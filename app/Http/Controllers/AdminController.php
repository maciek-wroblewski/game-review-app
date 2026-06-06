<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Post;
use App\Models\Notification;

class AdminController extends Controller
{
    public function index()
    {
        $userCount = User::count();

        $postCount = Post::count();

        $reviewCount = Post::whereHas('review')->count();

        $notificationCount = Notification::count();

        $latestUsers = User::latest()
            ->take(5)
            ->get();

        $latestPosts = Post::latest()
            ->with('author')
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
        $user->update(['verified' => !$user->verified]);
        return back();
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back(); // Prevent removing own admin status
        }
        
        $user->update(['is_admin' => !$user->is_admin]);
        return back();
    }

    public function toggleSuspend(User $user)
    {
        if ($user->id === auth()->id()) {
            return back(); // Prevent suspending self
        }
        
        $user->update(['is_suspended' => !$user->is_suspended]);
        return back();
    }

    public function togglePinned(Post $post)
    {
        $post->update(['is_pinned' => !$post->is_pinned]);
        
        if (request()->ajax() || request()->wantsJson()) {
            $post->load(['author.avatar', 'media', 'review', 'hub']);
            if (auth()->check()) {
                $post->loadExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            }
            $html = view('components.post', compact('post'))->render();
            return response()->json([
                'success' => true,
                'is_pinned' => $post->is_pinned,
                'html' => $html
            ]);
        }
        
        return back();
    }

    public function toggleLock(Post $post)
    {
        $post->update(['admin_locked' => !$post->admin_locked]);
        
        if (request()->ajax() || request()->wantsJson()) {
            $post->load(['author.avatar', 'media', 'review', 'hub']);
            if (auth()->check()) {
                $post->loadExists(['likes as liked_by_auth' => function ($q) {
                    $q->where('user_id', auth()->id());
                }]);
            }
            $html = view('components.post', compact('post'))->render();
            return response()->json([
                'success' => true,
                'admin_locked' => $post->admin_locked,
                'html' => $html
            ]);
        }
        
        return back();
    }
}