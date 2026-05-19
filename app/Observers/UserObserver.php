<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Playlist;
use Illuminate\Support\Facades\DB;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        // Wrap everything into a single transaction to guarantee atomicity and database speed
        DB::transaction(function () use ($user) {
            $user->settings()->create([
                'comments' => 'everyone',
                'dms' => 'mutuals',
            ]);

            $defaultPlaylists = ['Playing', 'Completed', 'Dropped', 'Wishlist'];

            foreach ($defaultPlaylists as $name) {
                $playlist = Playlist::create([
                    'name' => $name,
                    'is_system' => true,
                    'is_public' => true,
                ]);
                
                $user->playlists()->attach($playlist->id, ['role' => 'owner']);
            }
        });

    }
    public function deleting(User $user): void
    {
        // Find all playlists where this user is the "owner"
        $ownedPlaylists = $user->playlists()->wherePivot('role', 'owner')->get();

        // Delete the actual playlist records
        // (This will also trigger the cascade to remove pivot records for other users if the list was shared)
        foreach ($ownedPlaylists as $playlist) {
            $playlist->delete();
        }
    }
}