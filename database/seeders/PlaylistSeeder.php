<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Playlist;

class PlaylistSeeder extends Seeder
{
    public function run(): void
    {
        // Generate 10 random custom lists
        Playlist::factory(10)->create();
    }
}