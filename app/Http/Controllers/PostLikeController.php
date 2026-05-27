<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\LikesPostMail;

class PostLikeController extends Controller
{
    public function store(Post $post, Request $request)
    {
        $userId = auth()->id(); // Safe to assume this exists due to blade @auth / route middleware

        $post->toggleLike($userId);
        
        if ($post->user_id !== $userId) {
            Notification::create([
                'user_id' => $post->user_id,
                'from_user_id' => $userId,
                'type' => 'like',
                'message' => __(':username liked your post.', ['username' => auth()->user()->username]),
                'target_url' => url('/posts/' . $post->id),
            ]);
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => $post->likes()->where('user_id', $userId)->exists() ? 'liked' : 'unliked',
                'likes_count' => $post->likes()->count()
            ]);
        }

        // send a mail to the author of the post on the 10th like
        if ($post->likes()->count() == 10 && $post->author && $post->author->email) {
            Mail::to($post->author->email)->send(new LikesPostMail($post->author, $post));
        }

        return back();
    }
}
