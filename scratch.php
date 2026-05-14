<?php
$users = App\Models\User::all();
foreach($users as $user) {
    if($user->playlists()->count() == 0) {
        $defaultPlaylists = ['Playing', 'Completed', 'Dropped', 'Wishlist'];
        foreach($defaultPlaylists as $name) {
            $playlist = App\Models\Playlist::create(['name' => $name, 'is_system' => true, 'is_public' => true]);
            $user->playlists()->attach($playlist->id, ['role' => 'owner']);
        }
    }
}
echo "Done\n";
