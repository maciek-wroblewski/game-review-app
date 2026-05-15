<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'parent_id', 'body', 'is_locked', 'is_spoiler', 'likes_count'
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'is_spoiler' => 'boolean',
        ];
    }

    // Who wrote it?
    public function author()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Where does it live? (Game hub, User Profile, etc.)
    public function hub()
    {
        return $this->morphTo();
    }

    // What is it replying to?
    public function parent()
    {
        return $this->belongsTo(Post::class, 'parent_id');
    }
    
    public function media()
    {
        return $this->hasMany(Media::class);
    }

    // What are the replies to this post?
    public function replies()
    {
        return $this->hasMany(Post::class, 'parent_id');
    }

    // Who liked it?
    public function likes()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes')->withTimestamps();
    }

    public function toggleLike($userId)
    {
        // toggle() returns an array like: ['attached' => [1], 'detached' => []]
        $changes = $this->likes()->toggle($userId);

        // If a record was attached (created), increment the cache
        if (!empty($changes['attached'])) {
            $this->increment('likes_count', 1);
        }
        
        // If a record was detached (deleted), decrement the cache
        if (!empty($changes['detached'])) {
            $this->decrement('likes_count', 1);
        }
    }

    public function review()
    {
        return $this->hasOne(Review::class);
    }

    // Helper to check if this post is a review/article
    
    public function isReview(): bool
    {
        return $this->review()->exists();
    }
}