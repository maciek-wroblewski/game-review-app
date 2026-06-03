<?php

namespace App\Listeners;

use App\Events\PostCreated;
use App\Models\Notification;
use App\Mail\NewPostMail;
use App\Mail\NewCommentMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendPostNotifications implements ShouldQueue
{
    public function handle(PostCreated $event): void
    {
        $post = $event->post;
        $currentUser = $event->user;
        $parentPost = $event->parentPost;

        if ($parentPost) {
            if ($parentPost->user_id && $parentPost->user_id !== $currentUser->id) {
                Notification::create([
                    'user_id' => $parentPost->user_id,
                    'from_user_id' => $currentUser->id,
                    'type' => 'comment',
                    'message' => __(':username commented on your post.', ['username' => $currentUser->username]),
                    'target_url' => url('/posts/' . $parentPost->id),
                    'post_id' => $post->id,
                ]);

                if ($parentPost->author && $parentPost->author->email) {
                    Mail::to($parentPost->author->email)->queue(new NewCommentMail($currentUser, $post, $parentPost));
                }
            }
        } else {
            $currentUser->followers()->chunk(100, function ($followers) use ($currentUser, $post) {
                foreach ($followers as $follower) {
                    Notification::create([
                        'user_id' => $follower->id,
                        'from_user_id' => $currentUser->id,
                        'type' => 'new_post',
                        'message' => __(':username just posted a new post.', ['username' => $currentUser->username]),
                        'target_url' => url('/posts/' . $post->id),
                        'post_id' => $post->id,
                    ]);

                    Mail::to($follower->email)->queue(new NewPostMail($currentUser, $post));
                }
            });
        }
    }
}