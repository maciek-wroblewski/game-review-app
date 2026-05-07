<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Playlist;
use App\Models\User;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        // Get our admin user (ID 1)
        $admin = User::find(1, ['id']);

        // Generate the 4 system lists for the Admin
        $systemLists = ['Completed', 'Playing', 'Dropped', 'Wishlist'];
        foreach ($systemLists as $listName) {
            $list = Playlist::create([
                'name' => $listName,
                'is_system' => true,
                'is_public' => true,
            ]);
            // Attach list to admin as 'owner'
            $admin->playlists()->attach($list->id, ['role' => 'owner']);
        }

        // Generate 10 random custom lists
        Playlist::factory(10)->create();
    }
}