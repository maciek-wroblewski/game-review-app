<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Models\Media;
use App\Models\Notification;
use Illuminate\Support\Facades\DB;

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
            'is_admin' => 'boolean',

        ];
    }
    protected $fillable = [
        'username',
        'email',
        'password',
        'bio',
        'verified',
        'is_admin',
        'is_suspended',
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
        return $this->hasMany(Post::class)
            ->doesntHave('review');
    }

    public function reviews()
    {
        return $this->hasMany(Post::class)
            ->has('review');
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

    public function avatar()
    {
        return $this->belongsTo(Media::class, 'avatar_media_id');
    }

    /**
     * Scope: Load only the counts needed for compact user card layout.
     * Used in search results, follower lists, etc.
     * Reduces 5 count queries to 3 when compact layout is rendered.
     */
    public function scopeWithCompactCounts($query)
    {
        return $query->withCount(['reviews', 'followers', 'following']);
    }

    /**
     * Scope: Load all counts needed for full user card layout.
     * Used in user profiles, detailed views.
     */
    public function scopeWithFullCounts($query)
    {
        return $query->withCount(['reviews', 'followers', 'following', 'posts', 'playlists']);
    }

    public function getAvatarUrlAttribute()
    {
        // 1. Check if the 'avatar' relationship is already loaded (Media object)
        if ($this->relationLoaded('avatar') && $this->getRelationValue('avatar')) {
            return $this->getRelationValue('avatar')->file_path;
        }

        // 2. If 'avatar' is just a raw string column on the users table (e.g., from an older migration or OAuth)
        if (array_key_exists('avatar', $this->attributes) && is_string($this->attributes['avatar'])) {
            return $this->attributes['avatar'];
        }

        // 3. Fallback: try to fetch the relation value if it exists but wasn't explicitly caught above
        $media = $this->getRelationValue('avatar');
        
        return $media ? $media->file_path : null;
    }
}
