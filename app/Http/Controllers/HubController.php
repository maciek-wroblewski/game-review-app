<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Post;
use App\Models\Comment;
use Illuminate\Support\Facades\Auth;

class HubController extends Controller
{
    public function index()
    {
        $posts = Post::with(['user', 'game', 'comments.user'])->latest()->get();
        return view('hub.index', compact('posts'));
    }

    public function upvote(Post $post)
    {
        $post->increment('upvotes');
        return back();
    }

    public function comment(Request $request, Post $post)
    {
        $request->validate(['body' => 'required|string|max:1000']);
        
        $post->comments()->create([
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);

        return back();
    }
}
