<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use App\Models\Notification;
use Illuminate\Support\Facades\Mail;
use App\Mail\LikesPostMail;
use Illuminate\Support\Facades\Log;

class PostLikeController extends Controller
{
    public function store(Post $post, Request $request)
    {
        $userId = auth()->id();

        // toggleLike returns true if liked, false if unliked (2 queries: toggle + atomic increment/decrement)
        $isLiked = $post->toggleLike($userId);

        // Refresh to get updated likes_count (1 query)
        $freshPost = $post->fresh();

        if ($isLiked) {
            Log::info("User {$userId} liked Post {$post->id}");
        } else {
            Log::info("User {$userId} unliked Post {$post->id}");
            Notification::where([
                'user_id' => $post->user_id,
                'from_user_id' => $userId,
                'type' => 'like',
                'target_url' => url('/posts/' . $post->id),
            ])->delete();
        }

        // Notification - only create if this is a new like
        if ($isLiked && $post->user_id !== $userId) {
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
                'status' => $isLiked ? 'liked' : 'unliked',
                'likes_count' => $freshPost->likes_count,
            ]);
        }

        // Send mail on milestone (10th like) - use the already-refreshed count
        if ($freshPost->likes_count == 10 && $post->author && $post->author->email) {
            Mail::to($post->author->email)->send(new LikesPostMail($post->author, $post));
        }

        return back();
    }
}
