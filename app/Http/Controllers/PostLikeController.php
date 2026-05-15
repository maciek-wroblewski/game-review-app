<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;

class PostLikeController extends Controller
{
    public function store(Post $post, Request $request)
    {
        $userId = auth()->id(); // Safe to assume this exists due to blade @auth / route middleware

        $post->toggleLike($userId);

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $post->likes()->where('user_id', $userId)->exists() ? 'liked' : 'unliked',
                'likes_count' => $post->likes()->count()
            ]);
        }

        return back();
    }
}
