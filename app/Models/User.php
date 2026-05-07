<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable([
        'username',
        'email',
        'password',
        'bio',
        'avatar',
        'verified',
    ])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'verified' => 'boolean',
        ];
    }
    protected $fillable = [
        'username',
        'email',
        'password',
        'bio',
        'avatar',
        'verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

// ==========================================
    // RELATIONSHIPS
    // ==========================================

    public function settings()
    {
        return $this->hasOne(UserSetting::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    // --- Blocks ---
    public function blockedUsers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocker_user_id', 'blocked_user_id')->withTimestamps();
    }

    public function blockers()
    {
        return $this->belongsToMany(User::class, 'blocks', 'blocked_user_id', 'blocker_user_id')->withTimestamps();
    }

    // --- Followers / Mutuals ---
    public function following()
    {
        // Users this user is following
        return $this->morphedByMany(User::class, 'followable', 'follows', 'user_id', 'followable_id')->withTimestamps();
    }

    public function followers()
    {
        return $this->morphToMany(User::class, 'followable', 'follows', 'followable_id', 'user_id')->withTimestamps();
    }

    public function friends()
    {
        // The moots trick
        return $this->morphedByMany(User::class, 'followable', 'follows', 'user_id', 'followable_id')
            ->whereIn('users.id', function($query) {
                $query->select('user_id')
                      ->from('follows')
                      ->where('followable_type', User::class)
                      ->where('followable_id', $this->id);
            });
    }

    public function isMutualWith(User $targetUser): bool 
    {
        return $this->following()->where('followable_id', $targetUser->id)->exists() &&
               $this->followers()->where('user_id', $targetUser->id)->exists();
    }

    // --- Likes ---
    public function likedPosts()
    {
        return $this->morphedByMany(Post::class, 'likeable', 'likes')->withTimestamps();
    }

    // --- Game Hubs & Lists ---
    public function creditedGames()
    {
        return $this->belongsToMany(Game::class, 'credits')->withPivot('role')->withTimestamps();
    }

    public function playlists() 
    {
        return $this->belongsToMany(Playlist::class, 'playlist_user', 'user_id', 'playlist_id')->withPivot('role')->withTimestamps();
    }
    // --- Messaging ---
    public function conversations()
    {
        return $this->belongsToMany(Conversation::class, 'conversation_user')->withPivot('last_read_at')->withTimestamps();
    }
}
