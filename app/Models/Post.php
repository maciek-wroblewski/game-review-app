<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Post extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id', 'parent_id', 'body', 'format_type', 
        'rating', 'is_locked', 'likes_count'
    ];

    protected function casts(): array
    {
        return [
            'is_locked' => 'boolean',
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
}