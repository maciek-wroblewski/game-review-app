<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

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

    public function mutuals()
    {
        return $this->following()
            ->whereIn('users.id', function ($query) {
                $query->select('user_id')
                      ->from('follows')
                      ->where('followable_type', static::class)
                      ->where('followable_id', $this->id);
            });
    }

    public function isMutualWith(User $targetUser): bool 
    {
        return $this->mutuals()->where('users.id', $targetUser->id)->exists();
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

    protected static function booted(): void
    {
        static::created(function ($user) {
            $user->settings()->create([
                'comments' => 'everyone',
                'dms' => 'mutuals',
            ]);

            $defaultPlaylists = ['Playing', 'Completed', 'Dropped', 'Wishlist'];
            foreach ($defaultPlaylists as $name) {
                $playlist = \App\Models\Playlist::create([
                    'name' => $name,
                    'is_system' => true,
                    'is_public' => true,
                ]);
                $user->playlists()->attach($playlist->id, ['role' => 'owner']);
            }
        });
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class)
            ->latest();
    }

    public function canViewProfile(?User $viewer = null): bool
    {
        $visibility = $this->settings->profile_visibility ?? 'public';

        if ($visibility === 'public') {
            return true;
        }

        if (!$viewer) {
            return false;
        }

        if ($viewer->id === $this->id) {
            return true;
        }

        if ($visibility === 'followers') {

            return $this->followers()
                ->where('user_id', $viewer->id)
                ->exists();
        }

        if ($visibility === 'mutuals') {

            return $this->isMutualWith($viewer);
        }

        if ($visibility === 'private') {
            return false;
        }

        return false;
    }
}
