<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Genre extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug'];

    // Auto-generate the slug when creating a genre
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($genre) {
            $genre->slug = Str::slug($genre->name);
        });
    }

    public function games()
    {
        return $this->belongsToMany(Game::class, 'game_genre');
    }
}