<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\Game;
use Illuminate\Database\Seeder;

class GameSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Create some recognizable staple games for testing
        Game::factory()->create([
            'title' => 'Elden Ring',
            'details' => 'An action role-playing game developed by FromSoftware. It features a vast open world, deep lore, and challenging combat.',
            'release_date' => '2022-02-25',
            'cover_img' => 'https://cdn2.steamgriddb.com/thumb/557fa68027943a8b0d3b66c4e72ff23b.jpg',
            'banner_img' => 'https://cdn2.steamgriddb.com/hero_thumb/d1fcdf15cb97c47d0ed1e1e10773ae36.jpg',
            'logo' => 'https://cdn2.steamgriddb.com/logo_thumb/69f5d5c1249e17f4ac1d5b716db47105.png',
        ]);

        Game::factory()->create([
            'title' => 'Hollow Knight',
            'details' => 'A beautifully hand-drawn Metroidvania with tough-as-nails combat and a sprawling underground insect kingdom to explore.',
            'release_date' => '2017-02-24',
        ]);

        Game::factory()->create([
            'title' => 'Stardew Valley',
            'details' => 'A cozy farming simulation game where you inherit your grandfather\'s farm and build a life in Pelican Town.',
            'release_date' => '2016-02-26',
        ]);

        // 2. Generate 30 random filler games
        Game::factory(30)->create();
    }
}