<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'title',
        'details',
        'release_date',
        'cover_img',
        'logo',
        'banner_img',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            // This ensures Laravel automatically treats this as a Carbon Date object
            'release_date' => 'date', 
        ];
    }

    // ==========================================
    // RELATIONSHIPS
    // ==========================================

    // 1. The Hub Posts (Reviews, Questions, Guides)
    public function posts()
    {
        return $this->morphMany(Post::class, 'hub');
    }

    // 2. The Taxonomy (Genres)
    public function genres()
    {
        return $this->belongsToMany(Genre::class, 'game_genre');
    }

    // 3. The Credits (Developers, Publishers, Composers)
    public function credits()
    {
        return $this->belongsToMany(User::class, 'credits')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    // 4. Followers (Users subscribed to this game hub)
    public function followers()
    {
        return $this->morphToMany(User::class, 'followable', 'follows')
                    ->withTimestamps();
    }

    // 5. User Lists (What lists this game appears on)
    // Note: Assuming your List model is named GameList to avoid PHP reserved word 'List'
    public function playlists()
    {
        return $this->belongsToMany(Playlist::class, 'playlist_game', 'game_id', 'playlist_id')
                    ->withPivot('order')
                    ->withTimestamps();
    }
}