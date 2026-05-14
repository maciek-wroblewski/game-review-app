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

    protected function game(): \Illuminate\Database\Eloquent\Casts\Attribute
    {
        return \Illuminate\Database\Eloquent\Casts\Attribute::make(
            get: fn () => $this->post ? $this->post->hub : null,
        );
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->post->user();
    }

    protected static function booted()
    {
        static::observe(ReviewObserver::class);
    }
}