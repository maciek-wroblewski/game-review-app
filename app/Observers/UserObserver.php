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
                    'is_public' => false,
                    'description' => __("Default playlist: $name"),
                    'cover' => null,
                ]);
                
                $user->playlists()->attach($playlist->id, ['role' => 'owner']);
            }
        });

    }
    public function deleting(User $user): void
    {
        $ownedPlaylists = $user->playlists()->wherePivot('role', 'owner')->get();

        foreach ($ownedPlaylists as $playlist) {
            $playlist->delete();
        }
    }
}