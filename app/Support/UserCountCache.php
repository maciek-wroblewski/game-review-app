<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Collection;

/**
 * Request-scoped cache for user count data.
 * Prevents duplicate database queries when the same user's counts are needed multiple times per request.
 * 
 * Usage:
 *   UserCountCache::remember($userId, fn() => $user->reviews_count)
 *   or use in a middleware to auto-populate
 */
class UserCountCache
{
    private static array $cache = [];

    /**
     * Remember a user's counts for the duration of the request.
     */
    public static function remember(int $userId, callable $loader): array
    {
        if (isset(self::$cache[$userId])) {
            return self::$cache[$userId];
        }

        $result = call_user_func($loader);
        self::$cache[$userId] = $result;

        return $result;
    }

    /**
     * Bulk-populate cache with user collection that already has counts loaded.
     * Useful after a query like: User::withCompactCounts()->get()
     */
    public static function populateFromCollection(Collection $users): void
    {
        foreach ($users as $user) {
            if (
                isset($user->reviews_count, $user->followers_count, $user->following_count)
                || $user->isDirty() === false
            ) {
                self::$cache[$user->id] = [
                    'reviews_count' => $user->reviews_count ?? 0,
                    'followers_count' => $user->followers_count ?? 0,
                    'following_count' => $user->following_count ?? 0,
                    'posts_count' => $user->posts_count ?? 0,
                    'playlists_count' => $user->playlists_count ?? 0,
                ];
            }
        }
    }

    /**
     * Get all cached counts for a user.
     */
    public static function get(int $userId): ?array
    {
        return self::$cache[$userId] ?? null;
    }

    /**
     * Clear the cache (called at end of request or explicitly).
     */
    public static function clear(): void
    {
        self::$cache = [];
    }

    /**
     * Get cache hit count (for debugging).
     */
    public static function count(): int
    {
        return count(self::$cache);
    }
}
