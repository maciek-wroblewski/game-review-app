<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = [
        'user_id',
        'from_user_id',
        'type',
        'message',
        'read',
        'target_url',
        'post_id',
    ];

    protected static function booted()
    {
        static::saved(function ($notification) {
            \Illuminate\Support\Facades\Cache::forget("user_{$notification->user_id}_notifications_data");
        });

        static::deleted(function ($notification) {
            \Illuminate\Support\Facades\Cache::forget("user_{$notification->user_id}_notifications_data");
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fromUser()
    {
        return $this->belongsTo(User::class, 'from_user_id');
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function getMessageAttribute($value)
    {
        $username = $this->fromUser ? $this->fromUser->username : '';

        switch ($this->type) {
            case 'follow':
                return __('notifications.started_following', ['username' => $username]);
            case 'comment':
                return __('notifications.commented_on_post', ['username' => $username]);
            case 'new_post':
                return __('notifications.posted_new_post', ['username' => $username]);
            case 'like':
                return __('notifications.liked_post', ['username' => $username]);
            default:
                return $value;
        }
    }
}