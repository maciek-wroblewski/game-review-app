<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    protected $guarded = [];

    public function genres(): BelongsToMany
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    public function developers(): BelongsToMany
    {
        return $this->belongsToMany(Developer::class, 'game_developer');
    }

    public function publishers(): BelongsToMany
    {
        return $this->belongsToMany(Publisher::class, 'game_publisher');
    }

    public function platforms(): BelongsToMany
    {
        return $this->belongsToMany(Platform::class, 'game_platform');
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('status', 'personal_rating', 'recommendation_rating', 'review_text')->withTimestamps();
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
