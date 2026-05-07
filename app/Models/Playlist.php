<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Playlist extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'is_system', 'is_public'];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'playlist_user', 'playlist_id', 'user_id')
                    ->withPivot('role')
                    ->withTimestamps();
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'playlist_game', 'playlist_id', 'game_id')
                    ->withPivot('order')
                    ->withTimestamps();
    }

    public function likes()
    {
        return $this->morphToMany(User::class, 'likeable', 'likes')->withTimestamps();
    }
}