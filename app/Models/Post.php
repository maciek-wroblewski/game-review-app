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
        'admin_locked', // <-- Added
        'is_pinned',    // <-- Added
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
            'admin_locked' => 'boolean', // <-- Added
            'is_spoiler' => 'boolean',
            'is_pinned' => 'boolean',    // <-- Added        
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
        // toggle() returns ['attached' => [...], 'detached' => [...]]
        $changes = $this->likes()->toggle($userId);

        // If a record was attached (created), increment the cache
        if (! empty($changes['attached'])) {
            $this->increment('likes_count', 1);
            return true; // liked
        }

        // If a record was detached (deleted), decrement the cache
        if (! empty($changes['detached'])) {
            $this->decrement('likes_count', 1);
            return false; // unliked
        }

        return null; // no change
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

    public function scopeWithFeedRelations($query, array $options = [])
    {
        $loadReview = $options['review'] ?? true;
        $loadHub = $options['hub'] ?? true;
        $loadAuthor = $options['author'] ?? true;

        $relations = [
            'media',
            'parent' => function ($q) use ($loadReview, $loadHub) {
                $q->withCount('replies')
                    ->with([
                        'author' => function ($sq) {
                            // Minimal author data for nested posts (no counts needed)
                            $sq->with('avatar');
                        },
                        'media',
                    ])
                    ->when($loadReview, fn($q) => $q->with('review'))
                    ->when($loadHub, fn($q) => $q->with('hub'))
                    ->withLikedByAuth();
            },
        ];

        // Only load the top-level author if requested
        if ($loadAuthor) {
            $relations['author'] = function ($q) {
                $q->with('avatar')->withCount(['followers', 'following', 'posts']);
            };
        }

        if ($loadReview) {
            $relations[] = 'review';
        }
        
        if ($loadHub) {
            $relations[] = 'hub';
        }

        return $query->with($relations)
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

    /**
     * Minimal feed relations - loads only what's needed to render posts without author stats.
     * Use for game discussions, playlist discussions, etc. where author counts aren't displayed.
     * Saves 2-3 queries per user by not loading: followers_count, following_count, posts_count.
     */
    public function scopeWithMinimalFeedRelations($query, array $options = [])
    {
        $loadReview = $options['review'] ?? true;
        $loadHub = $options['hub'] ?? true;
        $loadAuthor = $options['author'] ?? true;

        $relations = [
            'media',
            'parent' => function ($q) use ($loadReview, $loadHub) {
                $q->withCount('replies')
                    ->with([
                        'author' => function ($sq) {
                            $sq->with('avatar'); // No counts
                        },
                        'media',
                    ])
                    ->when($loadReview, fn($q) => $q->with('review'))
                    ->when($loadHub, fn($q) => $q->with('hub'))
                    ->withLikedByAuth();
            },
        ];

        if ($loadAuthor) {
            $relations['author'] = function ($q) {
                $q->with('avatar'); // No counts for minimal feed
            };
        }

        if ($loadReview) {
            $relations[] = 'review';
        }
        
        if ($loadHub) {
            $relations[] = 'hub';
        }

        return $query->with($relations)
            ->withCount('replies')
            ->withLikedByAuth();
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
            
            // Delete notifications created in its creation
            \App\Models\Notification::where('post_id', $post->id)->delete();

            // You can also delete media here if you want to clean up files!
            // if ($post->media) { $post->media()->delete(); }
        });
    }
}
