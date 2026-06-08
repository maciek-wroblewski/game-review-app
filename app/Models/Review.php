<?php

namespace App\Models;

use App\Observers\ReviewObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Review extends Model
{
    use HasFactory;

    protected $fillable = ['post_id', 'type', 'rating'];

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->post->user();
    }

    public function getGameAttribute()
    {
        return $this->post->hub;
    }

    protected static function booted()
    {
        static::observe(ReviewObserver::class);
    }
}
