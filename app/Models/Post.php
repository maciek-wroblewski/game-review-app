<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'body',
        'hub_type',
        'hub_id',
        'parent_id',
        'is_spoiler',
        'is_locked',
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

    public function hub()
    {
        return $this->morphTo('hub', 'hub_type', 'hub_id');
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
        if (! empty($changes['attached'])) {
            $this->increment('likes_count', 1);
        }

        // If a record was detached (deleted), decrement the cache
        if (! empty($changes['detached'])) {
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
        if ($this->relationLoaded('review')) {
            return $this->review !== null;
        }

        return $this->review()->exists();
    }

    public function scopeWithFeedRelations($query)
    {
        return $query->with([
            // ⚡ Eager load the author's avatar AND pre-compute all 3 popover aggregate counters
            'author' => function ($q) {
                $q->with('avatar')->withCount(['followers', 'following', 'posts']);
            },
            'media',
            'review',
            'hub',
            // ⚡ Apply the exact same optimization to parent/quoted posts to satisfy their popovers too
            'parent' => function ($q) {
                $q->withCount('replies')
                    ->with([
                        'author' => function ($sq) {
                            $sq->with('avatar')->withCount(['followers', 'following', 'posts']);
                        },
                        'media',
                        'review',
                        'hub'
                    ])
                    ->withLikedByAuth();
            },
        ])
            ->withCount('replies')
            ->withLikedByAuth();
    }

    /**
     * Scope a query to dynamically check if the authenticated user has liked the posts.
     */
    public function scopeWithLikedByAuth($query)
    {
        return $query->when(auth()->check(), function ($q) {
            $q->withExists(['likes as liked_by_auth' => function ($sq) {
                $sq->where('user_id', auth()->id());
            }]);
        });
    }

    protected static function booted()
    {
        // Hook into the deleting event
        static::deleting(function ($post) {
            // Check if the post has a review, and explicitly delete it via Eloquent
            if ($post->review) {
                $post->review->delete(); 
                // ^ This fires the deleted event in your ReviewObserver!
            }
            
            // You can also delete media here if you want to clean up files!
            // if ($post->media) { $post->media()->delete(); }
        });
    }
}
